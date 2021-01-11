<?php

namespace Omadonex\LaravelAcl\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Omadonex\LaravelAcl\Classes\ConstantsAcl;
use Omadonex\LaravelAcl\Models\PermissionGroup;
use Omadonex\LaravelAcl\Models\PermissionGroupTranslate;
use Omadonex\LaravelAcl\Models\PermissionTranslate;
use Omadonex\LaravelAcl\Models\RoleTranslate;
use Omadonex\LaravelAcl\Models\Permission;
use Omadonex\LaravelAcl\Models\Role;
use Omadonex\LaravelSupport\Classes\ConstantsCustom;

class Generate extends Command
{
    const NWIDART_CLASS = '\Nwidart\Modules\Facades\Module';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omx:acl:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all data for acl based on config files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!file_exists(resource_path('lang/vendor/acl')) || !file_exists(base_path('config/acl.php'))) {
            $this->error('Error: main config and lang files are not published!');

            return ;
        }

        Role::protectedGenerate()->delete();
        RoleTranslate::protectedGenerate()->delete();
        Permission::truncate();
        PermissionGroup::truncate();
        PermissionGroupTranslate::truncate();
        PermissionTranslate::truncate();
        \DB::table('acl_pivot_permission_role')->where(ConstantsCustom::DB_FIELD_PROTECTED_GENERATE, true)->delete();

        $aclEntries = [
            ['configPath' => base_path('config/acl.php'), 'langPath' => resource_path('lang/vendor/acl'), 'module' => false],
        ];
        if (class_exists(self::NWIDART_CLASS)) {
            foreach (\Nwidart\Modules\Facades\Module::all() as $module) {
                $configPath = $module->getExtraPath('Config/acl/acl.php');
                $langPath = $module->getExtraPath('Config/acl/lang');
                if (file_exists($configPath)) {
                    $aclEntries[] = [
                        'configPath' => $configPath,
                        'langPath' => $langPath,
                        'module' => true,
                    ];
                }
            }
        }

        Model::unguard();

        $allPermissions = [];
        foreach ($aclEntries as $aclEntry) {
            $config = include $aclEntry['configPath'];
            $langPath = $aclEntry['langPath'];
            $rolesConfig = array_key_exists('roles', $config) ? $config['roles'] : [];
            $permissionsConfig = array_key_exists('permissions', $config) ? $config['permissions'] : [];
            $permissionsGroupsConfig = array_key_exists('permissionsGroups', $config) ? $config['permissionsGroups'] : [];
            $extendConfig = array_key_exists('extend', $config) ? $config['extend'] : [];

            $langKeys = array_diff(scandir($langPath), ['.', '..']);

            foreach ($permissionsConfig as $permissionConfig) {
                $allPermissions[] = $permissionConfig['id'];
                $createData = ['id' => $permissionConfig['id']];
                if (isset($permissionConfig['permission_group_id'])) {
                    $createData['permission_group_id'] = $permissionConfig['permission_group_id'];
                }
                Permission::create($createData);

                foreach ($langKeys as $lang) {
                    $langFile = include "{$langPath}/{$lang}/permissions.php";
                    PermissionTranslate::create([
                        'model_id' => $permissionConfig['id'],
                        'lang' => $lang,
                        'name' => $langFile[$permissionConfig['id']]['name'],
                        'description'  => $langFile[$permissionConfig['id']]['description'],
                    ]);
                }
            }

            if (!$aclEntry['module']) {
                array_unshift($permissionsGroupsConfig,
                    ['id' => ConstantsAcl::PERMISSION_GROUP_ID_DEFAULT]
                );
            }

            foreach ($permissionsGroupsConfig as $permissionGroupConfig) {
                PermissionGroup::create([
                    'id' => $permissionGroupConfig['id'],
                ]);

                foreach ($langKeys as $lang) {
                    $langFile = include "{$langPath}/{$lang}/permissionsGroups.php";
                    PermissionGroupTranslate::create([
                        'model_id' => $permissionGroupConfig['id'],
                        'lang' => $lang,
                        'name' => $langFile[$permissionGroupConfig['id']]['name'],
                        'description'  => $langFile[$permissionGroupConfig['id']]['description'],
                    ]);
                }
            }

            if (!$aclEntry['module']) {
                array_unshift($rolesConfig,
                    ['id' => ConstantsAcl::ROLE_USER],
                    ['id' => ConstantsAcl::ROLE_ROOT, 'staff' => true]
                );
            }

            foreach ($rolesConfig as $roleConfig) {
                $staff = array_key_exists('staff', $roleConfig) ? $roleConfig['staff'] : false;
                $root = $roleConfig['id'] === ConstantsAcl::ROLE_ROOT;

                $role = Role::create([
                    'id' => $roleConfig['id'],
                    'is_root' => $root,
                    'is_staff' => $staff,
                    ConstantsCustom::DB_FIELD_PROTECTED_GENERATE => true,
                ]);

                foreach ($langKeys as $lang) {
                    $langFile = include "{$langPath}/{$lang}/roles.php";
                    RoleTranslate::create([
                        'model_id' => $roleConfig['id'],
                        'lang' => $lang,
                        'name' => $langFile[$roleConfig['id']]['name'],
                        'description'  => $langFile[$roleConfig['id']]['description'],
                        ConstantsCustom::DB_FIELD_PROTECTED_GENERATE => true,
                    ]);
                }

                if (array_key_exists('permissions', $roleConfig)) {
                    $permissions = $roleConfig['permissions'];
                    foreach ($permissions as $permission) {
                        $role->permissions()->attach($permission, [ConstantsCustom::DB_FIELD_PROTECTED_GENERATE => true]);
                    }
                }
            }

            foreach ($extendConfig as $roleKey => $permissionsKeys) {
                Role::find($roleKey)->permissions()->attach($permissionsKeys, [ConstantsCustom::DB_FIELD_PROTECTED_GENERATE => true]);
            }
        }

        \DB::table('acl_pivot_permission_role')->whereNotIn('permission_id', $allPermissions)->delete();
        \DB::table('acl_pivot_permission_user')->whereNotIn('permission_id', $allPermissions)->delete();

        Model::reguard();
    }
}
