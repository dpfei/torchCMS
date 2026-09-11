<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksResourcePermissions;

class AdminPolicy
{
    use ChecksResourcePermissions;

    protected string $permissionPrefix = 'admin';
}
