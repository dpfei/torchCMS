<?php

namespace App\Http\Controllers;

use App\Support\EnvWriter;
use App\Support\Installer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class InstallController extends Controller
{
    /**
     * 第一步：环境自检
     */
    public function environment(): View
    {
        return view('install.environment', [
            'step' => 1,
            'checks' => Installer::environmentChecks(),
            'passed' => Installer::environmentPassed(),
        ]);
    }

    /**
     * 第二步：数据库配置表单
     */
    public function database(): View|RedirectResponse
    {
        if (! Installer::environmentPassed()) {
            return redirect()->route('install.environment');
        }

        $env = EnvWriter::forApplication();
        $env->ensureExists();

        return view('install.database', [
            'step' => 2,
            'values' => [
                'driver' => $env->get('DB_CONNECTION', 'mysql') ?? 'mysql',
                'host' => $env->get('DB_HOST', '127.0.0.1') ?? '127.0.0.1',
                'port' => $env->get('DB_PORT', '3306') ?? '3306',
                'database' => $env->get('DB_DATABASE', 'torchcms') ?? 'torchcms',
                'username' => $env->get('DB_USERNAME', 'root') ?? 'root',
                'password' => $env->get('DB_PASSWORD', '') ?? '',
            ],
        ]);
    }

    /**
     * 第二步：测试连接并写入 .env
     */
    public function storeDatabase(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'driver' => ['required', 'in:mysql,pgsql,sqlite'],
            'host' => ['nullable', 'string', 'max:120'],
            'port' => ['nullable', 'string', 'max:10'],
            'database' => ['required', 'string', 'max:200'],
            'username' => ['nullable', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'max:200'],
        ], [], $this->attributes());

        $driver = $data['driver'];

        $config = [
            'driver' => $driver,
            'host' => $data['host'] ?: '127.0.0.1',
            'port' => $data['port'] ?: ($driver === 'pgsql' ? '5432' : '3306'),
            'database' => $driver === 'sqlite' ? $this->resolveSqlitePath($data['database']) : $data['database'],
            'username' => $data['username'] ?? '',
            'password' => $data['password'] ?? '',
        ];

        $result = Installer::testConnection($config, $request->boolean('create_database'));

        if (! $result['ok']) {
            return back()->withInput()->withErrors(['database' => $result['message']]);
        }

        $env = EnvWriter::forApplication();

        if (! $env->ensureExists() || ! $env->isWritable()) {
            return back()->withInput()->withErrors(['database' => '无法写入 .env 文件，请检查项目根目录权限。']);
        }

        $values = ['DB_CONNECTION' => $driver];

        if ($driver === 'sqlite') {
            $values['DB_DATABASE'] = $config['database'];
        } else {
            $values['DB_HOST'] = $config['host'];
            $values['DB_PORT'] = $config['port'];
            $values['DB_DATABASE'] = $config['database'];
            $values['DB_USERNAME'] = $config['username'];
            $values['DB_PASSWORD'] = $config['password'];
        }

        if (! $env->get('APP_KEY')) {
            $values['APP_KEY'] = 'base64:'.base64_encode(random_bytes(32));
        }

        $env->set($values);

        $request->session()->put('install.database_configured', true);
        $request->session()->put('install.database_message', $result['message']);

        return redirect()->route('install.site');
    }

    /**
     * 第三步：站点与管理员信息表单
     */
    public function site(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get('install.database_configured')) {
            return redirect()->route('install.database');
        }

        return view('install.site', [
            'step' => 3,
            'databaseMessage' => $request->session()->get('install.database_message'),
            'values' => [
                'site_name' => 'torchCMS',
                'site_description' => '基于 Laravel 的内容管理系统',
                'site_url' => url('/'),
                'admin_name' => '超级管理员',
                'admin_email' => Installer::DEFAULT_ADMIN_EMAIL,
            ],
        ]);
    }

    /**
     * 第三步：执行安装
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->session()->get('install.database_configured')) {
            return redirect()->route('install.database');
        }

        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:60'],
            'site_description' => ['nullable', 'string', 'max:200'],
            'site_url' => ['nullable', 'url', 'max:120'],
            'admin_name' => ['required', 'string', 'max:60'],
            'admin_email' => ['required', 'email', 'max:120'],
            'admin_password' => ['required', 'string', 'min:6', 'max:60', 'confirmed'],
        ], [
            'admin_password.confirmed' => '两次输入的管理员密码不一致。',
        ], $this->attributes());

        $data['production'] = $request->boolean('production');
        $data['demo_content'] = $request->boolean('demo_content');

        try {
            $logs = Installer::install($data);
        } catch (Throwable $exception) {
            return back()->withInput()->withErrors(['install' => '安装失败：'.$exception->getMessage()]);
        }

        $request->session()->put('install.completed', true);
        $request->session()->put('install.logs', $logs);

        return redirect()->route('install.finish');
    }

    /**
     * 第四步：安装完成
     */
    public function finish(Request $request): View
    {
        return view('install.finish', [
            'step' => 4,
            'meta' => Installer::meta(),
            'logs' => $request->session()->pull('install.logs', []),
            'instant' => (bool) $request->session()->pull('install.completed'),
            'adminUrl' => url('/admin/login'),
            'homeUrl' => url('/'),
        ]);
    }

    /**
     * 表单字段中文名
     *
     * @return array<string, string>
     */
    protected function attributes(): array
    {
        return [
            'driver' => '数据库类型',
            'host' => '数据库地址',
            'port' => '端口',
            'database' => '数据库名',
            'username' => '数据库账号',
            'password' => '数据库密码',
            'site_name' => '站点名称',
            'site_description' => '站点简介',
            'site_url' => '站点地址',
            'admin_name' => '管理员姓名',
            'admin_email' => '管理员邮箱',
            'admin_password' => '管理员密码',
        ];
    }

    protected function resolveSqlitePath(string $database): string
    {
        if ($database === ':memory:') {
            return database_path('database.sqlite');
        }

        if (str_starts_with($database, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $database)) {
            return $database;
        }

        return base_path($database);
    }
}
