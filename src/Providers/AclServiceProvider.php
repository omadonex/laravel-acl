<?php

namespace Omadonex\LaravelAcl\Providers;

use Illuminate\Support\ServiceProvider;
use Omadonex\LaravelAcl\Commands\Generate;

class AclServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $pathRoot = realpath(__DIR__.'/../..');

        $this->loadTranslationsFrom("{$pathRoot}/resources/lang", 'acl');
        $this->loadMigrationsFrom("{$pathRoot}/database/migrations");

        $this->publishes([
            "{$pathRoot}/config/role.php" => config_path('acl/role.php'),
            "{$pathRoot}/config/permission.php" => config_path('acl/permission.php'),
            "{$pathRoot}/config/route.php" => config_path('acl/route.php'),
        ], 'config');

        $this->publishes([
            "{$pathRoot}/resources/lang" => resource_path('lang/vendor/acl'),
        ], 'translations');

        $this->commands([
            Generate::class,
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
