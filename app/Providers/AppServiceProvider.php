<?php

namespace App\Providers;

use App\Models\Admin;
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
        // 超级管理员拥有全部权限
        Gate::before(function ($user, string $ability) {
            if ($user instanceof Admin && $user->hasRole('super_admin')) {
                return true;
            }

            return null;
        });
    }
}
