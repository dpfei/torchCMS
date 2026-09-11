<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksResourcePermissions;

class NewsPolicy
{
    use ChecksResourcePermissions;

    protected string $permissionPrefix = 'news';
}
