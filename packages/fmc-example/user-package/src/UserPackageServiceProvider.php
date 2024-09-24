<?php

namespace FmcExample\UserPackage;

use Illuminate\Support\ServiceProvider;


class UserPackageServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('FmcExample\UserPackage\Http\Controllers\UserController');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadRoutesFrom(__DIR__.'/routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadSeedsFrom(__DIR__ . '/../database/seeders');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'userpackage');
    }

    protected function loadSeedsFrom($path)
    {
        if (class_exists('Seeder')) {
            require_once $path . '/UserSeeder.php';
        }
    }
}
