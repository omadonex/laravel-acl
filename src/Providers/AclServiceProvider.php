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
            "{$pathRoot}/config/acl.php" => config_path('acl.php'),
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
