<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Installer;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * 每个资源对应的可分配动作，最终权限名为 {ability}_{resource}
     *
     * @var array<string, array<int, string>>
     */
    protected array $resources = [
        'news' => ['view', 'create', 'update', 'delete'],
        'category' => ['view', 'create', 'update', 'delete'],
        'page' => ['view', 'create', 'update', 'delete'],
        'menu' => ['view', 'create', 'update', 'delete'],
        'media' => ['view', 'create', 'update', 'delete'],
        'role' => ['view', 'create', 'update', 'delete'],
        'admin' => ['view', 'create', 'update', 'delete'],
        'setting' => ['view', 'create', 'update', 'delete'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. 创建全部权限
        foreach ($this->resources as $resource => $abilities) {
            foreach ($abilities as $ability) {
                Permission::query()->firstOrCreate([
                    'name' => "{$ability}_{$resource}",
                    'guard_name' => 'admin',
                ]);
            }
        }

        $allPermissions = Permission::query()->where('guard_name', 'admin')->get();

        // 2. 超级管理员：拥有全部权限
        $superAdmin = Role::query()->firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'admin',
        ]);
        $superAdmin->syncPermissions($allPermissions);

        // 3. 内容编辑：可管理栏目与内容，无删除/系统设置权限
        $editor = Role::query()->firstOrCreate([
            'name' => 'editor',
            'guard_name' => 'admin',
        ]);
        $editor->syncPermissions(
            Permission::query()
                ->where('guard_name', 'admin')
                ->whereIn('name', [
                    'view_news', 'create_news', 'update_news',
                    'view_category', 'create_category', 'update_category',
                    'view_page', 'create_page', 'update_page',
                    'view_menu', 'create_menu', 'update_menu',
                    'view_media', 'create_media', 'update_media',
                ])
                ->get()
        );

        // 4. 只读角色
        $viewer = Role::query()->firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'admin',
        ]);
        $viewer->syncPermissions(
            Permission::query()
                ->where('guard_name', 'admin')
                ->where('name', 'like', 'view_%')
                ->get()
        );

        // 5. 默认超级管理员账号：密码随机生成，避免留下公开的弱口令账号
        //    网页安装向导随后会用用户在第 3 步填写的邮箱与密码覆盖同名账号
        $defaultPassword = Installer::generatePassword();

        $admin = Admin::query()->firstOrCreate(
            ['email' => Installer::DEFAULT_ADMIN_EMAIL],
            [
                'name' => '超级管理员',
                'password' => $defaultPassword,
            ]
        );

        $admin->syncRoles([$superAdmin]);

        if ($admin->wasRecentlyCreated && $this->command) {
            $this->command->warn('已创建默认管理员：'.Installer::DEFAULT_ADMIN_EMAIL.'，初始密码：'.$defaultPassword);
            $this->command->warn('请登录后台后立即修改该密码。');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
