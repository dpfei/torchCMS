<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksResourcePermissions;

class SettingPolicy
{
    use ChecksResourcePermissions;

    protected string $permissionPrefix = 'setting';
}
