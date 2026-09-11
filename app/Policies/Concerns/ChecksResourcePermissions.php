<?php

namespace App\Policies\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * 将 Policy 的标准动作映射到 spatie 权限。
 * 每个使用该 trait 的 Policy 需定义 $permissionPrefix，如 'news'，
 * 对应权限名为 view_news / create_news / update_news / delete_news。
 */
trait ChecksResourcePermissions
{
    public function viewAny(?Model $user, mixed $record = null): bool
    {
        return $this->allowsResource($user, 'view');
    }

    public function view(?Model $user, mixed $record = null): bool
    {
        return $this->allowsResource($user, 'view');
    }

    public function create(?Model $user, mixed $record = null): bool
    {
        return $this->allowsResource($user, 'create');
    }

    public function update(?Model $user, mixed $record = null): bool
    {
        return $this->allowsResource($user, 'update');
    }

    public function delete(?Model $user, mixed $record = null): bool
    {
        return $this->allowsResource($user, 'delete');
    }

    public function deleteAny(?Model $user, mixed $record = null): bool
    {
        return $this->allowsResource($user, 'delete');
    }

    public function restore(?Model $user, mixed $record = null): bool
    {
        return $this->allowsResource($user, 'delete');
    }

    public function restoreAny(?Model $user, mixed $record = null): bool
    {
        return $this->allowsResource($user, 'delete');
    }

    public function forceDelete(?Model $user, mixed $record = null): bool
    {
        return $this->allowsResource($user, 'delete');
    }

    public function forceDeleteAny(?Model $user, mixed $record = null): bool
    {
        return $this->allowsResource($user, 'delete');
    }

    protected function allowsResource(?Model $user, string $ability): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'checkPermissionTo') && $user->checkPermissionTo($ability . '_' . $this->permissionPrefix)) {
            return true;
        }

        return method_exists($user, 'hasRole') && $user->hasRole('super_admin');
    }
}
