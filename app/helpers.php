<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * 读取系统设置项
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}
