<?php

namespace App\Providers;

use App\Auth\SaltedEloquentUserProvider;
use App\Models\Admin;
use App\Models\MenuItem;
use App\Support\Navigation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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

        // 页头与页脚的导航统一取自「导航菜单」，
        // 集中在这里注入，控制器不必各自查一遍
        View::composer('partials.header', function (\Illuminate\View\View $view): void {
            $view->with('menu', Navigation::items(MenuItem::LOCATION_HEADER));
        });

        View::composer('partials.footer', function (\Illuminate\View\View $view): void {
            $view->with('menu', Navigation::items(MenuItem::LOCATION_FOOTER));
        });
    }
}
