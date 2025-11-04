<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasDateTimeFormatterTrait;

class News extends Model
{
    use HasFactory, HasDateTimeFormatterTrait;

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
     * 日期字段
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * 类型转换
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'cat_id' => 'integer',
        'title' => 'string',
        'thumb' => 'string',
        'keywords' => 'string',
        'description' => 'string',
        'external_url' => 'string',
        'sort' => 'integer',
        'status' => 'integer',
        'input_time' => 'datetime',
        'content' => 'string',
        'readpoint' => 'integer',
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
     * 获取创建人关联
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用',
        ];
    }
}
