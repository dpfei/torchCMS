<?php

namespace App\Support;

use App\Models\MenuItem;
use Illuminate\Support\Collection;

/**
 * 前台导航数据源。
 *
 * 页头与页脚都从这里取菜单，集中一处便于缓存，也避免各控制器各查一遍。
 */
class Navigation
{
    /**
     * 指定位置的菜单项
     *
     * @return Collection<int, MenuItem>
     */
    public static function items(string $location): Collection
    {
        return ContentCache::remember(
            'menus.' . $location,
            fn () => MenuItem::query()
                ->location($location)
                ->published()
                ->with(['category', 'page'])
                ->orderBy('sort')
                ->orderBy('id')
                ->get()
        );
    }
}
