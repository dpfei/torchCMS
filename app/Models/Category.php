<?php

namespace App\Models;

use App\Support\ContentCache;
use App\Traits\HasDateTimeFormatterTrait;
use App\Traits\HasSlugTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasDateTimeFormatterTrait, HasSlugTrait;

    protected $table = 'categories';

    protected $fillable = [
        'id',
        'parent_id',
        'cat_name',
        'slug',
        'description',
        'thumb',
        'sort',
        'is_menu',
    ];

    /**
     * 前台路由使用 slug 作为绑定字段
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * 生成别名的来源字段
     */
    public function slugSource(): string
    {
        return 'cat_name';
    }

    /**
     * 兜底别名前缀
     */
    public function slugFallbackPrefix(): string
    {
        return 'category';
    }

    /**
     * 栏目变更后让前台列表缓存整体失效
     */
    protected static function booted(): void
    {
        $flush = static fn () => ContentCache::flush();

        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * 父级栏目关系
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * 子级栏目关系
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * 栏目下的内容
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'cat_id', 'id');
    }

    /**
     * 仅展示在前台菜单的栏目
     */
    public function scopeMenus(Builder $query): Builder
    {
        return $query->where('is_menu', 1)->orderBy('sort');
    }

    /**
     * 封面图访问地址
     */
    protected function thumbUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (blank($this->thumb)) {
                return null;
            }

            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('public');

            return $disk->url($this->thumb);
        });
    }

    /**
     * 获取栏目选项列表
     */
    public static function getOptionList(): array
    {
        return self::query()->pluck('cat_name', 'id')->toArray();
    }
}
