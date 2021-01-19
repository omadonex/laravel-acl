<?php

namespace Omadonex\LaravelAcl\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Nwidart\Modules\Facades\Module;
use Omadonex\LaravelAcl\Classes\ConstAcl;
use Omadonex\LaravelAcl\Models\PermissionGroup;
use Omadonex\LaravelAcl\Models\PermissionGroupTranslate;
use Omadonex\LaravelAcl\Models\PermissionTranslate;
use Omadonex\LaravelAcl\Models\RoleTranslate;
use Omadonex\LaravelAcl\Models\Permission;
use Omadonex\LaravelAcl\Models\Role;
use Omadonex\LaravelSupport\Classes\ConstCustom;

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
        if (!file_exists(resource_path('lang/vendor/acl'))
            || !file_exists(base_path('config/acl/role.php'))
            || !file_exists(base_path('config/acl/permission.php'))) {
            $this->error('Error: main config and lang files are not published!');

            return ;
        }

        Role::protectedGenerate()->delete();
        RoleTranslate::protectedGenerate()->delete();
        Permission::truncate();
        PermissionGroup::truncate();
        PermissionGroupTranslate::truncate();
        PermissionTranslate::truncate();
        \DB::table('acl_pivot_permission_role')->where(ConstCustom::DB_FIELD_PROTECTED_GENERATE, true)->delete();

        $aclEntryList = [
            [
                'configRole' => base_path('config/acl/role.php'),
                'configPermission' => base_path('config/acl/permission.php'),
                'langPath' => resource_path('lang/vendor/acl'),
                'module' => 'app',
            ],
        ];

        if (class_exists(self::NWIDART_CLASS)) {
            foreach (Module::all() as $module) {
                $configPath = $module->getExtraPath('Config/acl');
                $aclEntryList[] = [
                    'configRole' => "{$configPath}/role.php",
                    'configPermission' => "{$configPath}/permission.php",
                    'langPath' => $module->getExtraPath('Config/acl/lang'),
                    'module' => $module->getLowerName(),
                ];
            }
        }

        Model::unguard();

        $permissionList = [];
        $roleList = [];
        foreach ($aclEntryList as $aclEntry) {
            $configRole = file_exists($aclEntry['configRole']) ? include $aclEntry['configRole'] : [];
            $configPermission = file_exists($aclEntry['configPermission']) ? include $aclEntry['configPermission'] : [];
            $langPath = $aclEntry['langPath'];
            if (file_exists($langPath)) {
                $langKeyList = array_diff(scandir($langPath), ['.', '..']);
            } else {
                $langKeyList = [app()->currentLocale()];
            }

            $permissionList = array_merge($permissionList, $this->createPermission($configPermission, $langPath, $langKeyList, $aclEntry['module']));
            $roleList = array_merge($roleList, $this->createRole($configRole, $langPath, $langKeyList, $aclEntry['module']));
        }

        $roleIdList = Role::all()->pluck('role_id')->toArray();
        \DB::table('acl_pivot_permission_role')->whereNotIn('permission_id', $permissionList)->delete();
        \DB::table('acl_pivot_permission_user')->whereNotIn('permission_id', $permissionList)->delete();
        \DB::table('acl_pivot_role_user')->whereNotIn('role_id', $roleIdList)->delete();

        Model::reguard();
    }

    /**
     * @param array $data
     * @param string $langPath
     * @param array $langKeyList
     * @param string $module
     * @return array
     */
    private function createRole(array $data, string $langPath, array $langKeyList, string $module): array
    {
        $createdList = [];

        if ($module === 'app') {
            $data[ConstAcl::ROLE_USER] = [];
            $data[ConstAcl::ROLE_ROOT] = ['staff' => true];
        }

        foreach ($data as $roleId => $roleData) {
            $createdList[] = $roleId;

            $role = Role::create([
                'id' => $roleId,
                'is_root' => $roleId === ConstAcl::ROLE_ROOT,
                'is_staff' => $roleData['staff'] ?? false,
                ConstCustom::DB_FIELD_PROTECTED_GENERATE => true,
            ]);

            foreach ($langKeyList as $lang) {
                $langFile = "{$langPath}/{$lang}/role.php";
                $langData = file_exists($langFile) ? include $langFile : [];
                RoleTranslate::create([
                    'model_id' => $roleId,
                    'lang' => $lang,
                    'name' => $langData[$roleId]['name'] ?? $roleId,
                    'description'  => $langData[$roleId]['description'] ?? $roleId,
                    ConstCustom::DB_FIELD_PROTECTED_GENERATE => true,
                ]);
            }

            foreach ($roleData['permissions'] ?? [] as $permission) {
                $role->permissions()->attach($permission, [ConstCustom::DB_FIELD_PROTECTED_GENERATE => true]);
            }
        }

        return $createdList;
    }

    /**
     * @param array $data
     * @param string $langPath
     * @param array $langKeyList
     * @param string $module
     * @param string|null $permissionGroupId
     * @return array
     */
    private function createPermission(array $data, string $langPath, array $langKeyList, string $module, string $permissionGroupId = null): array
    {
        $createdList = [];

        if ($permissionGroupId === null) {
            $permissionGroupId = $module;
            $this->createPermissionGroup($permissionGroupId, null, 0, $langPath, $langKeyList);
        }

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $permissionId = $value;
                $createdList[] = $permissionId;
                Permission::create([
                    'id' => $permissionId,
                    'permission_group_id' => $permissionGroupId,
                ]);

                foreach ($langKeyList as $lang) {
                    $langFile = "{$langPath}/{$lang}/permission.php";
                    $langData = file_exists($langFile) ? include $langFile : [];
                    PermissionTranslate::create([
                        'model_id' => $permissionId,
                        'lang' => $lang,
                        'name' => $langData[$permissionId]['name'] ?? $permissionId,
                        'description'  => $langData[$permissionId]['description'] ?? $permissionId,
                    ]);
                }
            } elseif (is_array($value)) {
                $groupId = $key;
                $permissionList = $value;
                $this->createPermissionGroup($groupId, $permissionGroupId, 0, $langPath, $langKeyList);
                $createdList = array_merge($createdList, $this->createPermission($permissionList, $langPath, $langKeyList, $module, $groupId));
            }
        }

        return $createdList;
    }

    /**
     * @param string $id
     * @param string|null $parentId
     * @param int $order
     * @param string $langPath
     * @param array $langKeyList
     */
    private function createPermissionGroup(string $id, ?string $parentId, int $order, string $langPath, array $langKeyList): void
    {
        if (!PermissionGroup::find($id)) {
            PermissionGroup::create([
                'id' => $id,
                'parent_id' => $parentId,
                'order' => $order,
            ]);

            foreach ($langKeyList as $lang) {
                $langFile = "{$langPath}/{$lang}/permissionGroup.php";
                $langData = file_exists($langFile) ? include $langFile : [];
                PermissionGroupTranslate::create([
                    'model_id' => $id,
                    'lang' => $lang,
                    'name' => $langData[$id]['name'] ?? $id,
                    'description' => $langData[$id]['description'] ?? $id,
                ]);
            }
        }
    }
}
