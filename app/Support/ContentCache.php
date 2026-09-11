<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * 前台内容缓存。
 *
 * 所有缓存键都带上「内容版本号」，内容发生增删改时只需递增版本号，
 * 旧缓存自然失效，无需遍历删除缓存键。
 */
class ContentCache
{
    /**
     * 版本号缓存键
     */
    public const VERSION_KEY = 'cms.content.version';

    /**
     * 默认缓存时间（秒）
     */
    public const TTL = 600;

    /**
     * 当前内容版本号
     */
    public static function version(): int
    {
        $version = Cache::get(self::VERSION_KEY);

        if (blank($version)) {
            $version = 1;
            Cache::forever(self::VERSION_KEY, $version);
        }

        return (int) $version;
    }

    /**
     * 读取缓存，未命中时执行回调并写入
     */
    public static function remember(string $key, Closure $callback, ?int $ttl = null): mixed
    {
        return Cache::remember(
            'cms.content.v' . static::version() . '.' . $key,
            $ttl ?? self::TTL,
            $callback
        );
    }

    /**
     * 内容变更：递增版本号，使全部前台缓存失效
     */
    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, static::version() + 1);
    }
}
