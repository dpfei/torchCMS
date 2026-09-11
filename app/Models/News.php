<?php

namespace App\Models;

use App\Support\ContentCache;
use App\Traits\HasDateTimeFormatterTrait;
use App\Traits\HasSlugTrait;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    use HasDateTimeFormatterTrait, HasFactory, HasSlugTrait, SoftDeletes;

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'news';

    /**
     * 可填充字段
     *
     * @var array
     */
    protected $fillable = [
        'cat_id',
        'title',
        'slug',
        'thumb',
        'keywords',
        'description',
        'external_url',
        'sort',
        'status',
        'input_time',
        'content',
        'readpoint',
        'copyfrom',
        'user_id',
    ];

    /**
     * 隐藏字段
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * 类型转换
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'cat_id' => 'integer',
        'title' => 'string',
        'slug' => 'string',
        'thumb' => 'string',
        'keywords' => 'string',
        'description' => 'string',
        'external_url' => 'string',
        'sort' => 'integer',
        'status' => 'integer',
        'content' => 'string',
        'readpoint' => 'integer',
        'views' => 'integer',
        'copyfrom' => 'string',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取分类关联
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'id');
    }

    /**
     * 获取创建人关联（后台管理员）
     */
    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id');
    }

    /**
     * 模型事件：自动补全发布人与发布时间
     */
    protected static function booted(): void
    {
        static::creating(function (News $news) {
            if (blank($news->input_time)) {
                $news->input_time = now();
            }

            if (blank($news->user_id)) {
                $news->user_id = Filament::auth()->id();
            }
        });

        // 内容变更后让前台列表缓存整体失效
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
     * 兜底别名前缀
     */
    public function slugFallbackPrefix(): string
    {
        return 'news';
    }

    /**
     * 录入时间：数据库存 Unix 时间戳，模型侧读写为 Carbon 实例
     */
    protected function inputTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?Carbon => filled($value) ? Carbon::createFromTimestamp((int) $value) : null,
            set: function ($value): int {
                if (blank($value)) {
                    return 0;
                }

                if ($value instanceof \DateTimeInterface) {
                    return $value->getTimestamp();
                }

                return is_numeric($value) ? (int) $value : Carbon::parse($value)->getTimestamp();
            },
        );
    }

    /**
     * 缩略图访问地址
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
     * 前台详情地址
     */
    protected function link(): Attribute
    {
        return Attribute::get(fn (): string => route('news.show', $this->slug ?: $this->getKey()));
    }

    /**
     * 已发布的文章
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用',
        ];
    }
}
