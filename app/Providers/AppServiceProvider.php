<?php

namespace App\Providers;

use App\Auth\SaltedEloquentUserProvider;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 管理员密码带应用级盐值，需要自定义的 Eloquent 提供者参与校验
        Auth::provider('salted-eloquent', function ($app, array $config) {
            return new SaltedEloquentUserProvider($app['hash'], $config['model']);
        });

        // 超级管理员拥有全部权限
        Gate::before(function ($user, string $ability) {
            if ($user instanceof Admin && $user->hasRole('super_admin')) {
                return true;
            }

            return null;
        });
    }
}
