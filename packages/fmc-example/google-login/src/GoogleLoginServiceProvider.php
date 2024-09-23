<?php

namespace FmcExample\GoogleLogin;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
class GoogleLoginServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('FmcExample\GoogleLogin\Http\Controllers\GoogleLoginController');
    }
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

    }
}
