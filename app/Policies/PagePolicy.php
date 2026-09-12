<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksResourcePermissions;

class PagePolicy
{
    use ChecksResourcePermissions;

    protected string $permissionPrefix = 'page';
}
