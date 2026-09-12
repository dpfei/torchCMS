<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksResourcePermissions;

class MenuItemPolicy
{
    use ChecksResourcePermissions;

    protected string $permissionPrefix = 'menu';
}
