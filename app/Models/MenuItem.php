<?php

namespace App\Models;

use App\Support\ContentCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * 导航菜单项。
 *
 * 前台页头与页脚的导航都只从这里取值：栏目、单页、自定义链接统一挂在菜单下，
 * 因此「谁排在第几位」只有一个来源，不会出现多套顺序互相打架。
 */
class MenuItem extends Model
{
    /**
     * 菜单位置
     */
    public const LOCATION_HEADER = 'header';
    public const LOCATION_FOOTER = 'footer';

    /**
     * 链接类型
     */
    public const TYPE_CUSTOM = 'custom';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_PAGE = 'page';

    /**
     * 打开方式
     */
    public const TARGET_SELF = '_self';
    public const TARGET_BLANK = '_blank';

    public const STATUS_DISABLED = 0;
    public const STATUS_ENABLED = 1;

    protected $table = 'menu_items';

    protected $fillable = [
        'location',
        'label',
        'type',
        'target_id',
        'url',
        'target',
        'sort',
        'status',
    ];

    protected $casts = [
        'target_id' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
    ];

    protected static function booted(): void
    {
        $flush = static fn () => ContentCache::flush();

        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * 菜单项指向的栏目
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'target_id');
    }

    /**
     * 菜单项指向的单页
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'target_id');
    }

    /**
     * 前台访问地址：按链接类型解析
     */
    protected function link(): Attribute
    {
        return Attribute::get(function (): string {
            return match ($this->type) {
                self::TYPE_CATEGORY => $this->category
                    ? route('category.show', $this->category)
                    : '#',
                self::TYPE_PAGE => $this->page?->link ?? '#',
                default => self::normalizeUrl($this->url),
            };
        });
    }

    /**
     * 自定义链接补全：外部地址与锚点原样使用，其余按站内根路径处理
     */
    public static function normalizeUrl(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return '#';
        }

        return Str::startsWith($url, ['http://', 'https://', '//', '/', '#', 'mailto:', 'tel:'])
            ? $url
            : '/' . $url;
    }

    /**
     * 已启用的菜单项
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    /**
     * 指定位置的菜单项
     */
    public function scopeLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', $location);
    }

    /**
     * @return array<string, string>
     */
    public static function getLocationOptions(): array
    {
        return [
            self::LOCATION_HEADER => '页头导航',
            self::LOCATION_FOOTER => '页脚导航',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_CUSTOM => '自定义链接',
            self::TYPE_CATEGORY => '栏目',
            self::TYPE_PAGE => '单页',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getTargetOptions(): array
    {
        return [
            self::TARGET_SELF => '当前窗口',
            self::TARGET_BLANK => '新窗口',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用',
        ];
    }
}
