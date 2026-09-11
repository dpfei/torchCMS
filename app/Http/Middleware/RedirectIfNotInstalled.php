<?php

namespace App\Http\Middleware;

use App\Support\Installer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 未完成安装时把请求引导到安装向导；已安装后关闭安装入口。
 */
class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        $isInstallRequest = $request->is('install') || $request->is('install/*');

        if ($isInstallRequest) {
            // 安装向导运行时尚无数据表，强制使用文件会话与数组缓存
            $this->useInstallerDrivers();
            // 确保 .env 与 APP_KEY 就绪，否则会话 Cookie 无法加密
            Installer::prepareEnvironment();
        }

        if (! Installer::isInstalled()) {
            $this->useInstallerDrivers();

            if (! $isInstallRequest) {
                return redirect()->route('install.environment');
            }
        } elseif ($isInstallRequest && ! $request->is('install/finish')) {
            return redirect('/admin');
        }

        return $next($request);
    }

    protected function useInstallerDrivers(): void
    {
        config([
            'session.driver' => 'file',
            'cache.default' => 'array',
        ]);
    }
}
