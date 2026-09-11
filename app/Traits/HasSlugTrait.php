<?php

namespace App\Traits;

use App\Support\SlugGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 为内容模型自动维护唯一的 URL 别名（slug）。
 *
 * 使用方可通过 slugSource() 指定生成别名的来源字段，
 * 通过 slugFallbackPrefix() 指定来源字段无法生成别名时的兜底前缀。
 */
trait HasSlugTrait
{
    protected static function bootHasSlugTrait(): void
    {
        static::saving(function (Model $model): void {
            $model->ensureSlugIsUnique();
        });
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
        return 'item';
    }

    /**
     * 保证当前记录的 slug 唯一且格式合法
     */
    protected function ensureSlugIsUnique(): void
    {
        $base = SlugGenerator::normalize($this->slug);

        if ($base === '') {
            $base = SlugGenerator::normalize((string) $this->getAttribute($this->slugSource()));
        }

        if ($base === '') {
            $base = SlugGenerator::fallback($this->slugFallbackPrefix());
        }

        $base = SlugGenerator::trim($base);

        $this->slug = SlugGenerator::unique($base, fn (string $candidate): bool => $this->slugExists($candidate));
    }

    /**
     * 判断某个别名是否已被其他记录占用（包含软删除记录，避免唯一索引冲突）
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::query();

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            $query = $query->withTrashed();
        }

        $query->where('slug', $slug);

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        return $query->exists();
    }

    /**
     * 按别名解析路由绑定，同时兼容 id 访问（旧链接与后台直达链接）
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();

        if ($field === $this->getKeyName()) {
            return $query->where($this->getQualifiedKeyName(), $value);
        }

        return $query->where(function ($query) use ($value, $field): void {
            $query->where($field, $value);

            if (is_numeric($value)) {
                $query->orWhere($this->getQualifiedKeyName(), $value);
            }
        });
    }
}
