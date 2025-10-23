<?php

namespace App\Models;

use App\Traits\HasDateTimeFormatterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasDateTimeFormatterTrait;

    protected $table = 'categories';

    protected $fillable = [
        'id',
        'parent_id',
        'cat_name',
        'description',
        'thumb',
        'sort',
        'is_menu',
    ];

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
     * 获取栏目选项列表
     */
    public static function getOptionList(): array
    {
        return self::query()->pluck('cat_name', 'id')->toArray();
    }
}
