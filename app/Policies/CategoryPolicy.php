<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksResourcePermissions;

class CategoryPolicy
{
    use ChecksResourcePermissions;

    protected string $permissionPrefix = 'category';
}
