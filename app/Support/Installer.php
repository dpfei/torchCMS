<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Role;
use App\Models\Setting;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PDO;
use Throwable;

/**
 * 安装引导核心：安装状态判定、环境自检、数据库连通性检测与安装执行。
 */
class Installer
{
    public const LOCK_FILE = 'installed.lock';

    public const MIN_PHP = '8.3.0';

    public const DEFAULT_ADMIN_EMAIL = 'admin@torchcms.com';

    /**
     * 生成初始管理员密码：字母 + 数字，不使用符号，便于复制与手抄
     */
    public static function generatePassword(int $length = 16): string
    {
        return Str::password($length, symbols: false);
    }

    /**
     * 安装锁文件路径
     */
    public static function lockPath(): string
    {
        return storage_path(self::LOCK_FILE);
    }

    /**
     * 是否已完成安装。
     *
     * 为避免老部署升级后被强制跳转到安装向导，锁文件缺失时
     * 会额外探测数据库中是否已存在管理员表，命中则自动补写锁文件。
     */
    public static function isInstalled(): bool
    {
        if (file_exists(self::lockPath())) {
            return true;
        }

        if (! self::detectExistingInstallation()) {
            return false;
        }

        self::markInstalled(['detected' => true]);

        return true;
    }

    /**
     * 探测历史部署（数据库已建表且存在管理员账号）
     */
    protected static function detectExistingInstallation(): bool
    {
        try {
            $env = EnvWriter::forApplication();

            if (! $env->exists()) {
                return false;
            }

            $connection = $env->get('DB_CONNECTION', 'sqlite');

            // 未填写数据库名时直接判定为未安装，避免无谓的连接失败日志
            if ($connection !== 'sqlite' && ! $env->get('DB_DATABASE')) {
                return false;
            }

            if ($connection === 'sqlite') {
                $database = $env->get('DB_DATABASE') ?: database_path('database.sqlite');

                if (! file_exists($database)) {
                    return false;
                }
            }

            if (! Schema::hasTable('admins') || ! Schema::hasTable('settings')) {
                return false;
            }

            return Admin::query()->exists();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * 安装向导运行前的环境准备。
     *
     * 全新环境通常没有 .env 与 APP_KEY，而会话 Cookie 需要密钥加密，
     * 因此这里先补齐 .env 并生成一个应用密钥。
     */
    public static function prepareEnvironment(): void
    {
        $env = EnvWriter::forApplication();

        if (! $env->exists()) {
            $env->ensureExists();
        }

        if (config('app.key')) {
            return;
        }

        $key = 'base64:'.base64_encode(random_bytes(32));

        if ($env->isWritable()) {
            $env->set(['APP_KEY' => $key]);
        }

        config(['app.key' => $key]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function meta(): array
    {
        if (! file_exists(self::lockPath())) {
            return [];
        }

        return json_decode((string) file_get_contents(self::lockPath()), true) ?: [];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function markInstalled(array $meta = []): void
    {
        $directory = dirname(self::lockPath());

        if (! is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        file_put_contents(self::lockPath(), json_encode(array_merge([
            'installed_at' => date('Y-m-d H:i:s'),
            'php' => PHP_VERSION,
            'cms_version' => '1.0.0',
        ], $meta), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * 环境自检项
     *
     * @return array<int, array<string, mixed>>
     */
    public static function environmentChecks(): array
    {
        $checks = [];

        $checks[] = self::check(
            '运行环境',
            'PHP 版本',
            PHP_VERSION,
            '>= '.self::MIN_PHP,
            version_compare(PHP_VERSION, self::MIN_PHP, '>=')
        );

        foreach (['ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml'] as $extension) {
            $loaded = extension_loaded($extension);

            $checks[] = self::check('PHP 扩展', $extension, $loaded ? '已启用' : '未启用', '必须启用', $loaded);
        }

        foreach (['gd', 'intl', 'zip', 'bcmath'] as $extension) {
            $loaded = extension_loaded($extension);

            $checks[] = self::check('PHP 扩展', $extension, $loaded ? '已启用' : '未启用', '建议启用', $loaded, 'optional');
        }

        $drivers = array_values(array_filter(['pdo_mysql', 'pdo_sqlite', 'pdo_pgsql'], 'extension_loaded'));

        $checks[] = self::check(
            '数据库',
            'PDO 驱动',
            $drivers === [] ? '未启用' : implode('、', $drivers),
            '至少启用一个',
            $drivers !== []
        );

        foreach (self::writablePaths() as $label => $path) {
            $checks[] = self::check('目录权限', $label, is_writable($path) ? '可写' : '不可写', '需要可写', is_writable($path));
        }

        $env = EnvWriter::forApplication();

        $checks[] = self::check(
            '配置文件',
            '.env',
            $env->exists() ? '已存在' : '将自动创建',
            '需要可写',
            $env->isWritable()
        );

        return $checks;
    }

    /**
     * @return array<string, string>
     */
    public static function writablePaths(): array
    {
        return [
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];
    }

    /**
     * 必需项是否全部通过
     */
    public static function environmentPassed(): bool
    {
        foreach (self::environmentChecks() as $check) {
            if ($check['level'] === 'required' && ! $check['passed']) {
                return false;
            }
        }

        return true;
    }

    /**
     * 检测数据库连通性，可选自动创建数据库（仅 MySQL）
     *
     * @param  array<string, string>  $config
     * @return array{ok: bool, message: string}
     */
    public static function testConnection(array $config, bool $createDatabase = false): array
    {
        $driver = $config['driver'] ?? 'mysql';

        try {
            if ($driver === 'sqlite') {
                $path = $config['database'];

                if (! is_dir(dirname($path))) {
                    @mkdir(dirname($path), 0755, true);
                }

                if (! file_exists($path)) {
                    @touch($path);
                }

                new PDO('sqlite:'.$path);

                return ['ok' => true, 'message' => 'SQLite 数据库可用：'.$path];
            }

            $dsn = sprintf('%s:host=%s;port=%s;dbname=%s', $driver, $config['host'], $config['port'], $config['database']);

            $pdo = self::makePdo($dsn, $config);

            return ['ok' => true, 'message' => '连接成功，服务端版本 '.$pdo->getAttribute(PDO::ATTR_SERVER_VERSION)];
        } catch (Throwable $exception) {
            if ($createDatabase && $driver === 'mysql' && self::isUnknownDatabase($exception)) {
                return self::createDatabase($config);
            }

            return ['ok' => false, 'message' => self::humanize($exception)];
        }
    }

    /**
     * @param  array<string, string>  $config
     * @return array{ok: bool, message: string}
     */
    protected static function createDatabase(array $config): array
    {
        try {
            $dsn = sprintf('mysql:host=%s;port=%s', $config['host'], $config['port']);

            $pdo = self::makePdo($dsn, $config);

            $name = str_replace('`', '', $config['database']);

            $pdo->exec("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            return ['ok' => true, 'message' => '数据库不存在，已自动创建并连接成功。'];
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => '自动创建数据库失败：'.$exception->getMessage()];
        }
    }

    /**
     * @param  array<string, string>  $config
     */
    protected static function makePdo(string $dsn, array $config): PDO
    {
        return new PDO($dsn, $config['username'] ?? null, $config['password'] ?? null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    protected static function isUnknownDatabase(Throwable $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'Unknown database')
            || str_contains($message, '1049')
            || str_contains($message, 'does not exist');
    }

    protected static function humanize(Throwable $exception): string
    {
        $message = $exception->getMessage();

        if (str_contains($message, 'Access denied')) {
            return '数据库账号或密码不正确：'.$message;
        }

        if (str_contains($message, 'Connection refused') || str_contains($message, 'getaddrinfo')) {
            return '无法连接到数据库服务，请确认地址与端口是否正确：'.$message;
        }

        return $message;
    }

    /**
     * 按 .env 中的配置装载数据库连接，供引导流程内即时使用
     */
    public static function applyDatabaseConfig(EnvWriter $env): void
    {
        $connection = $env->get('DB_CONNECTION', 'sqlite');

        config(['database.default' => $connection]);

        if ($connection === 'sqlite') {
            $database = $env->get('DB_DATABASE') ?: database_path('database.sqlite');

            if (! str_starts_with($database, '/') && ! preg_match('/^[A-Za-z]:[\\\\\/]/', $database)) {
                $database = base_path($database);
            }

            if (! is_dir(dirname($database))) {
                @mkdir(dirname($database), 0755, true);
            }

            if (! file_exists($database)) {
                @touch($database);
            }

            config(['database.connections.sqlite.database' => $database]);
        } else {
            $defaults = $connection === 'pgsql'
                ? ['host' => '127.0.0.1', 'port' => '5432', 'database' => 'torchcms', 'username' => 'postgres']
                : ['host' => '127.0.0.1', 'port' => '3306', 'database' => 'torchcms', 'username' => 'root'];

            config([
                "database.connections.{$connection}.driver" => $connection,
                "database.connections.{$connection}.host" => $env->get('DB_HOST', $defaults['host']),
                "database.connections.{$connection}.port" => $env->get('DB_PORT', $defaults['port']),
                "database.connections.{$connection}.database" => $env->get('DB_DATABASE', $defaults['database']),
                "database.connections.{$connection}.username" => $env->get('DB_USERNAME', $defaults['username']),
                "database.connections.{$connection}.password" => $env->get('DB_PASSWORD', ''),
            ]);

            if ($connection === 'mysql') {
                config([
                    "database.connections.mysql.charset" => 'utf8mb4',
                    "database.connections.mysql.collation" => 'utf8mb4_unicode_ci',
                ]);
            }
        }

        DB::purge($connection);
        DB::setDefaultConnection($connection);
    }

    /**
     * 执行安装：迁移数据表、初始化权限与设置、创建管理员、生成软链、写入安装锁
     *
     * @param  array<string, mixed>  $options
     * @return array<int, string>
     */
    public static function install(array $options): array
    {
        $env = EnvWriter::forApplication();
        $logs = [];

        self::applyDatabaseConfig($env);
        $logs[] = '数据库连接配置已就绪（'.$env->get('DB_CONNECTION', 'sqlite').'）';

        // 管理员密码盐：首次安装时自动生成并固化。重复安装必须沿用原值，
        // 否则数据库里已有的管理员密码会全部失效。
        $salt = (string) $env->get('ADMIN_PASSWORD_SALT');

        if ($salt === '') {
            $salt = Str::random(40);
            $env->set(['ADMIN_PASSWORD_SALT' => $salt]);
        }

        config(['admin.password_salt' => $salt]);

        Artisan::call('migrate', ['--force' => true]);
        $logs[] = '数据表结构迁移完成';

        Artisan::call('db:seed', ['--force' => true]);
        $logs[] = '已初始化权限、角色与系统设置';

        self::saveSiteSettings($options);
        $logs[] = '站点信息已写入';

        self::saveAdministrator($options);
        $logs[] = '管理员账号创建完成';

        $logs[] = self::createStorageLink();

        if (! empty($options['demo_content'])) {
            self::seedDemoContent();
            $logs[] = '演示栏目与内容导入完成';
        }

        if (! empty($options['production'])) {
            $env->set(['APP_ENV' => 'production', 'APP_DEBUG' => 'false']);
            $logs[] = '已切换为生产模式（关闭调试输出）';
        }

        // 站点名与独立的 Cookie 名一并落地，避免与同域名下的其它应用共用 laravel-session 互相覆盖
        $envValues = ['SESSION_COOKIE' => 'torchcms_session'];

        if (! empty($options['site_name'])) {
            $envValues['APP_NAME'] = $options['site_name'];
        }

        if (! empty($options['site_url'])) {
            $envValues['APP_URL'] = rtrim((string) $options['site_url'], '/');
        }

        $env->set($envValues);

        Artisan::call('optimize:clear');
        $logs[] = '缓存已清理';

        self::markInstalled([
            'site_name' => $options['site_name'] ?? null,
            'admin_email' => $options['admin_email'] ?? self::DEFAULT_ADMIN_EMAIL,
            'logs' => $logs,
        ]);

        return $logs;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    protected static function saveSiteSettings(array $options): void
    {
        if (! empty($options['site_name'])) {
            Setting::set('site_name', $options['site_name']);
        }

        if (! empty($options['site_description'])) {
            Setting::set('site_description', $options['site_description']);
        }
    }

    /**
     * 创建管理员：优先复用种子数据生成的默认账号，避免残留无用记录
     *
     * @param  array<string, mixed>  $options
     */
    protected static function saveAdministrator(array $options): void
    {
        // 优先复用与所填邮箱一致的账号，避免覆盖到不相干的历史管理员
        $admin = Admin::query()->where('email', $options['admin_email'])->first()
            ?? Admin::query()->orderBy('id')->first()
            ?? new Admin();

        $admin->fill([
            'name' => $options['admin_name'],
            'email' => $options['admin_email'],
            'password' => $options['admin_password'],
        ])->save();

        $role = Role::query()
            ->where('name', 'super_admin')
            ->where('guard_name', 'admin')
            ->first();

        if ($role) {
            $admin->syncRoles([$role]);
        }
    }

    protected static function createStorageLink(): string
    {
        if (file_exists(public_path('storage'))) {
            return '资源软链接已存在';
        }

        try {
            Artisan::call('storage:link');

            return '资源软链接创建完成';
        } catch (Throwable $exception) {
            return '资源软链接创建失败，可稍后手动执行 php artisan storage:link（'.$exception->getMessage().'）';
        }
    }

    public static function seedDemoContent(): void
    {
        Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function check(string $group, string $label, string $current, string $required, bool $passed, string $level = 'required'): array
    {
        return [
            'group' => $group,
            'label' => $label,
            'current' => $current,
            'required' => $required,
            'passed' => $passed,
            'level' => $level,
        ];
    }
}
