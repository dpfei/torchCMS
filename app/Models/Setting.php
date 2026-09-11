<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const CACHE_KEY = 'cms.settings';

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    protected static function booted(): void
    {
        $forget = static fn () => Cache::forget(self::CACHE_KEY);

        static::saved($forget);
        static::deleted($forget);
    }

    /**
     * 以 key => value 数组形式读取全部配置（带缓存）
     *
     * @return array<string, mixed>
     */
    public static function allAsArray(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return self::query()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * 读取单个配置
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::allAsArray()[$key] ?? $default;
    }

    /**
     * 写入单个配置
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }
}
