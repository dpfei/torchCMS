<?php

namespace App\Support;

use Illuminate\Support\Str;
use Throwable;

/**
 * URL 别名（slug）生成器：中文自动转拼音，统一输出小写中划线格式。
 */
class SlugGenerator
{
    /**
     * 把任意文本规范化为 slug
     */
    public static function normalize(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        // 含非 ASCII 字符（如中文）时先转拼音
        if (preg_match('/[^\x00-\x7F]/', $value)) {
            $value = static::transliterate($value);
        }

        return Str::slug($value);
    }

    /**
     * 生成不冲突的 slug
     *
     * @param  callable(string): bool  $exists  判断候选 slug 是否已被占用
     */
    public static function unique(string $base, callable $exists): string
    {
        $candidate = $base;
        $suffix = 2;

        while ($exists($candidate)) {
            $candidate = $base . '-' . $suffix++;
        }

        return $candidate;
    }

    /**
     * 来源文本无法生成有效 slug 时的兜底值
     */
    public static function fallback(string $prefix = 'item'): string
    {
        return $prefix . '-' . Str::lower(Str::random(8));
    }

    /**
     * 长度保护，避免超出字段长度
     */
    public static function trim(string $slug, int $length = 150): string
    {
        return Str::limit($slug, $length, '');
    }

    /**
     * 中文转拼音（未安装拼音库时退化为 ascii 转换）
     */
    protected static function transliterate(string $value): string
    {
        if (class_exists(\Overtrue\Pinyin\Pinyin::class)) {
            try {
                return \Overtrue\Pinyin\Pinyin::permalink($value, '-');
            } catch (Throwable) {
                // 转换失败时走通用兜底
            }
        }

        return Str::ascii($value);
    }
}
