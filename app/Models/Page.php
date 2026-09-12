<?php

namespace App\Models;

use App\Support\ContentCache;
use App\Traits\HasDateTimeFormatterTrait;
use App\Traits\HasSlugTrait;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * 单页：独立于栏目的内容，如「关于我们」「联系方式」「服务条款」。
 *
 * 与 News 的区别：没有栏目归属、不参与列表聚合、没有发布时间与点击量。
 * 前台通过根级地址直接访问（如 /about），可选出现在导航菜单中。
 */
class Page extends Model
{
    use HasDateTimeFormatterTrait, HasSlugTrait, SoftDeletes;

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'pages';

    /**
     * 可填充字段
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'thumb',
        'keywords',
        'description',
        'content',
        'sort',
        'is_menu',
        'status',
        'user_id',
    ];

    /**
     * 类型转换
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'slug' => 'string',
        'thumb' => 'string',
        'keywords' => 'string',
        'description' => 'string',
        'content' => 'string',
        'sort' => 'integer',
        'is_menu' => 'integer',
        'status' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取创建人关联（后台管理员）
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id');
    }

    /**
     * 模型事件：自动补全创建人，并在内容变更后清空前台缓存
     */
    protected static function booted(): void
    {
        static::creating(function (Page $page) {
            if (blank($page->user_id)) {
                $page->user_id = Filament::auth()->id();
            }
        });

        $flush = static fn () => ContentCache::flush();

        static::saved($flush);
        static::deleted($flush);
        static::restored($flush);
        static::forceDeleted($flush);
    }

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
        return 'title';
    }

    /**
     * 兜底别名前缀
     */
    public function slugFallbackPrefix(): string
    {
        return 'page';
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
     * 前台访问地址
     */
    protected function link(): Attribute
    {
        return Attribute::get(fn (): string => route('page.show', $this->slug ?: $this->getKey()));
    }

    /**
     * 已启用的单页
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用',
        ];
    }
}
