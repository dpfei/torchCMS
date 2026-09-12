<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * 系统设置项。
 *
 * 每一行既是「配置的定义」（key / label / type / group / sort），也是「配置的值」（value）。
 * 因为 key 与 value 同处一行，后台既可以直接在「设置项管理」里增删配置，
 * 「系统设置」页也能按分组把全部配置动态渲染成表单，无需再改代码。
 */
class Setting extends Model
{
    public const CACHE_KEY = 'cms.settings';

    /**
     * 输入类型
     */
    public const TYPE_TEXT = 'text';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_URL = 'url';
    public const TYPE_EMAIL = 'email';
    public const TYPE_IMAGE = 'image';
    public const TYPE_SWITCH = 'switch';

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
        'group',
        'sort',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    protected static function booted(): void
    {
        $forget = static fn () => Cache::forget(self::CACHE_KEY);

        static::saved($forget);
        static::deleted($forget);
    }

    /**
     * 按分组整理全部设置项，供后台动态渲染表单
     *
     * @return Collection<string, Collection<int, self>>
     */
    public static function grouped(): Collection
    {
        return self::query()
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->groupBy('group');
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
     *
     * 不传分组时保留该配置原有的分组，避免表单保存把分组冲掉。
     */
    public static function set(string $key, mixed $value, ?string $group = null): void
    {
        $setting = self::query()->firstOrNew(['key' => $key]);

        $setting->value = $value;

        if ($group !== null) {
            $setting->group = $group;
        }

        $setting->save();
    }

    /**
     * 输入类型选项
     *
     * @return array<string, string>
     */
    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_TEXT => '单行文本',
            self::TYPE_TEXTAREA => '多行文本',
            self::TYPE_URL => '网址',
            self::TYPE_EMAIL => '邮箱',
            self::TYPE_IMAGE => '图片',
            self::TYPE_SWITCH => '开关',
        ];
    }
}
