<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * 后台角色统一使用 admin 守卫
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'guard_name' => 'admin',
    ];

    protected const LABELS = [
        'super_admin' => '超级管理员',
        'editor' => '内容编辑',
        'viewer' => '只读访客',
    ];

    public function getLabelAttribute(): string
    {
        return self::LABELS[$this->name] ?? $this->name;
    }
}
