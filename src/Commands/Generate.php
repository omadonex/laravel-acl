<?php

namespace Omadonex\LaravelAcl\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Omadonex\LaravelAcl\Classes\ConstantsAcl;
use Omadonex\LaravelAcl\Models\PrivilegeTranslate;
use Omadonex\LaravelAcl\Models\RoleTranslate;
use Omadonex\LaravelAcl\Models\Privilege;
use Omadonex\LaravelAcl\Models\Role;
use Omadonex\LaravelSupport\Classes\ConstantsCustom;

class Generate extends Command
{
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
        $langPath = resource_path('lang/vendor/acl');
        $configPath = config_path('acl.php');
        if (!file_exists($langPath) || !file_exists($configPath)) {
            $this->error('Error: config and lang files are not published!');

            return ;
        }

        Role::protectedGenerate()->delete();
        RoleTranslate::protectedGenerate()->delete();
        Privilege::truncate();
        PrivilegeTranslate::truncate();
        \DB::table('acl_pivot_privilege_role')->where(ConstantsCustom::DB_FIELD_PROTECTED_GENERATE, true)->delete();


        $config = config('acl');
        $rolesConfig = $config['roles'];
        $privilegesConfig = $config['privileges'];

        $langKeys = array_diff(scandir($langPath), ['.', '..']);

        Model::unguard();

        foreach ($privilegesConfig as $privilegeConfig) {
            Privilege::create([
                'id' => $privilegeConfig['id'],
            ]);

            foreach ($langKeys as $lang) {
                $langFile = include "{$langPath}/{$lang}/privileges.php";
                PrivilegeTranslate::create([
                    'model_id' => $privilegeConfig['id'],
                    'lang' => $lang,
                    'name' => $langFile[$privilegeConfig['id']]['name'],
                    'description'  => $langFile[$privilegeConfig['id']]['description'],
                ]);
            }
        }

        array_unshift($rolesConfig,
            ['id' => ConstantsAcl::ROLE_USER],
            ['id' => ConstantsAcl::ROLE_ROOT, 'staff' => true]
        );

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

            if (array_key_exists('privileges', $roleConfig)) {
                $privileges = $roleConfig['privileges'];
                foreach ($privileges as $privilege) {
                    $role->privileges()->attach($privilege, [ConstantsCustom::DB_FIELD_PROTECTED_GENERATE => true]);
                }
            }
        }

        Model::reguard();
    }
}
