<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
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
        'media' => ['view', 'create', 'update', 'delete'],
        'role' => ['view', 'create', 'update', 'delete'],
        'admin' => ['view', 'create', 'update', 'delete'],
        'setting' => ['view', 'update'],
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

        // 5. 默认超级管理员账号
        $admin = Admin::query()->firstOrCreate(
            ['email' => 'admin@torchcms.com'],
            [
                'name' => '超级管理员',
                'password' => 'password',
            ]
        );
        $admin->syncRoles([$superAdmin]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
