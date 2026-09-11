<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksResourcePermissions;

class MediaPolicy
{
    use ChecksResourcePermissions;

    protected string $permissionPrefix = 'media';
}
