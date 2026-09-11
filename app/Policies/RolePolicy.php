<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksResourcePermissions;

class RolePolicy
{
    use ChecksResourcePermissions;

    protected string $permissionPrefix = 'role';
}
