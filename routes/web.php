<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\NewsController;
use App\Support\Captcha;
use Illuminate\Support\Facades\Route;

// 安装引导（无需登录，安装完成后自动关闭）
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'environment'])->name('environment');
    Route::get('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/database', [InstallController::class, 'storeDatabase'])->name('database.store');
    Route::get('/site', [InstallController::class, 'site'])->name('site');
    Route::post('/site', [InstallController::class, 'store'])->name('site.store');
    Route::get('/finish', [InstallController::class, 'finish'])->name('finish');
});

// 后台登录验证码图片（未登录可访问，取图即生成新题并写入会话）
Route::get('/captcha/admin-login', function () {
    abort_unless(Captcha::supportsImage(), 404);

    return Captcha::image();
})->name('captcha.admin-login');

// 前台（使用 slug 伪静态，模型侧兼容 id 访问）
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/news/{news:slug}', [NewsController::class, 'show'])->name('news.show');
