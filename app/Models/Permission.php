<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * 后台权限统一使用 admin 守卫
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'guard_name' => 'admin',
    ];

    /**
     * 动作与资源的显示名称
     */
    protected const ABILITY_LABELS = [
        'view' => '查看',
        'create' => '新增',
        'update' => '编辑',
        'delete' => '删除',
    ];

    protected const RESOURCE_LABELS = [
        'news' => '内容',
        'category' => '栏目',
        'media' => '媒体',
        'role' => '角色',
        'admin' => '管理员',
        'setting' => '系统设置',
    ];

    /**
     * 将权限标识（如 view_news）转换为中文名称（查看内容）
     */
    public static function labelFor(string $name): string
    {
        [$ability, $resource] = array_pad(explode('_', $name, 2), 2, '');

        $abilityLabel = self::ABILITY_LABELS[$ability] ?? $ability;
        $resourceLabel = self::RESOURCE_LABELS[$resource] ?? $resource;

        return $abilityLabel . $resourceLabel;
    }
}
