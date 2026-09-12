<?php

namespace App\Support;

use App\Models\Media;
use Closure;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 站点图片字段与媒体库之间的黏合层。
 *
 * 站点各处放图片的字段（新闻封面、单页封面、栏目缩略图、站点 Logo……）上传的文件
 * 都通过这里登记进媒体库，让媒体库成为全站图片的总览；反过来，这些字段只放行
 * 「自己的图片目录里真实存在」的路径，堵住把任意路径塞进表单的口子。
 */
class MediaLibrary
{
    /**
     * 允许被站点图片字段引用的目录（相对 public 磁盘）
     *
     * 与各处字段的 ->directory() 保持一致。
     *
     * @var array<int, string>
     */
    public const ALLOWED_DIRECTORIES = ['media/', 'news/', 'pages/', 'categories/', 'settings/'];

    /**
     * 把已经落盘的文件登记到媒体库（同一路径只登记一条）
     *
     * @param  string  $path  相对磁盘的路径，如 media/xxx.png
     * @param  string  $disk  磁盘名
     * @param  string|null  $name  上传时的原始文件名，作为媒体库里显示的名字
     */
    public static function index(string $path, string $disk = 'public', ?string $name = null): ?Media
    {
        $disk = $disk ?: 'public';

        // 磁盘上不存在的路径不入库，避免媒体库里出现打不开的空记录
        if (blank($path) || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        return Media::query()->firstOrCreate(
            ['path' => $path, 'disk' => $disk],
            array_filter(['name' => $name], static fn (mixed $value): bool => filled($value)),
        );
    }

    /**
     * 按路径查媒体库记录
     */
    public static function forPath(?string $path, string $disk = 'public'): ?Media
    {
        if (blank($path)) {
            return null;
        }

        return Media::query()
            ->where('path', $path)
            ->where('disk', $disk ?: 'public')
            ->first();
    }

    /**
     * 供 FileUpload::preventFilePathTampering() 使用的放行规则
     */
    public static function allowExistingFilePath(): Closure
    {
        return static fn (string $file): bool => static::isAllowedPath($file);
    }

    /**
     * 路径是否放行：必须落在站点的图片目录里，且文件真实存在
     *
     * 「存在」这一条覆盖了两种正当来源：媒体库里引用过来的文件，以及历史上传留下的文件。
     */
    public static function isAllowedPath(string $file): bool
    {
        if (blank($file) || str_contains($file, '..')) {
            return false;
        }

        if (! Str::startsWith($file, self::ALLOWED_DIRECTORIES)) {
            return false;
        }

        return Storage::disk('public')->exists($file);
    }
}
