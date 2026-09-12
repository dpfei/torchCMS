<?php

namespace App\Support;

use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\News\NewsResource;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Category;
use App\Models\Media;
use App\Models\News;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * 反查「这个文件被哪些内容用了」，用于媒体库列表展示与删除前预警。
 *
 * 图片在站点里有三种落地方式：
 * 1. 封面类字段（news.thumb / pages.thumb / categories.thumb）——存的就是路径；
 * 2. 系统设置的图片项（settings.value）——也是路径；
 * 3. 富文本正文里嵌的图——存的是完整 URL，URL 里包含同样的路径。
 */
class MediaUsage
{
    /**
     * 每类引用最多列这么多条，避免弹窗与提示过长
     */
    protected const LIMIT = 30;

    /**
     * 列出所有引用（含类型、字段、标题、后台编辑地址）
     *
     * @return Collection<int, array{type: string, field: string, title: string, url: string}>
     */
    public static function for(?Media $media): Collection
    {
        $path = (string) ($media?->path ?? '');

        if (blank($path)) {
            return collect();
        }

        $usages = collect();

        foreach (self::pathTargets() as $target) {
            self::collect($usages, $target, [$target['column'], $path]);
        }

        $like = '%' . self::escapeLike($path) . '%';

        foreach (self::contentTargets() as $target) {
            self::collect($usages, $target, [$target['column'], 'like', $like]);
        }

        return $usages;
    }

    /**
     * 引用条数
     */
    public static function count(?Media $media): int
    {
        return self::for($media)->count();
    }

    /**
     * 单条删除前的提醒文案
     */
    public static function deleteWarning(?Media $media): string
    {
        $usages = self::for($media);

        if ($usages->isEmpty()) {
            return '这个文件目前没有被任何内容引用，删除后记录与文件都会消失。';
        }

        $titles = $usages
            ->take(5)
            ->map(fn (array $usage): string => "{$usage['type']}《{$usage['title']}》（{$usage['field']}）")
            ->implode('、');

        return sprintf(
            '有 %d 处内容正在使用这个文件，删除后它们的图片会显示不出来：%s%s。',
            $usages->count(),
            $titles,
            $usages->count() > 5 ? ' 等' : '',
        );
    }

    /**
     * 批量删除前的提醒文案
     *
     * @param  Collection<int, Media>|array<int, Media>|null  $records
     */
    public static function bulkDeleteWarning(Collection|array|null $records): string
    {
        $used = collect($records)->filter(fn (Media $media): bool => self::for($media)->isNotEmpty());

        if ($used->isEmpty()) {
            return '这些文件目前都没有被内容引用，删除后记录与文件都会消失。';
        }

        return sprintf(
            '其中 %d 个文件正在被内容使用（%s%s），删除后对应图片会显示不出来。',
            $used->count(),
            $used->take(5)->map(fn (Media $media): string => (string) $media->name)->implode('、'),
            $used->count() > 5 ? ' 等' : '',
        );
    }

    /**
     * 直接存路径的图片字段
     *
     * @return array<int, array<string, mixed>>
     */
    protected static function pathTargets(): array
    {
        return [
            [
                'model' => News::class,
                'column' => 'thumb',
                'type' => '新闻',
                'field' => '封面',
                'title' => static fn (News $record): string => (string) $record->title,
                'url' => static fn (News $record): string => NewsResource::getUrl('edit', ['record' => $record]),
            ],
            [
                'model' => Page::class,
                'column' => 'thumb',
                'type' => '单页',
                'field' => '封面',
                'title' => static fn (Page $record): string => (string) $record->title,
                'url' => static fn (Page $record): string => PageResource::getUrl('edit', ['record' => $record]),
            ],
            [
                'model' => Category::class,
                'column' => 'thumb',
                'type' => '栏目',
                'field' => '缩略图',
                'title' => static fn (Category $record): string => (string) $record->cat_name,
                'url' => static fn (Category $record): string => CategoryResource::getUrl('edit', ['record' => $record]),
            ],
            [
                'model' => Setting::class,
                'column' => 'value',
                'type' => '系统设置',
                'field' => '图片项',
                'title' => static fn (Setting $record): string => (string) ($record->label ?: $record->key),
                'url' => static fn (Setting $record): string => ManageSettings::getUrl(),
            ],
        ];
    }

    /**
     * 富文本正文里可能嵌了这张图
     *
     * @return array<int, array<string, mixed>>
     */
    protected static function contentTargets(): array
    {
        return [
            [
                'model' => News::class,
                'column' => 'content',
                'type' => '新闻',
                'field' => '正文',
                'title' => static fn (News $record): string => (string) $record->title,
                'url' => static fn (News $record): string => NewsResource::getUrl('edit', ['record' => $record]),
            ],
            [
                'model' => Page::class,
                'column' => 'content',
                'type' => '单页',
                'field' => '正文',
                'title' => static fn (Page $record): string => (string) $record->title,
                'url' => static fn (Page $record): string => PageResource::getUrl('edit', ['record' => $record]),
            ],
        ];
    }

    /**
     * @param  Collection<int, array{type: string, field: string, title: string, url: string}>  $usages
     * @param  array<string, mixed>  $target
     * @param  array<int, mixed>  $where  where() 的原始参数
     */
    protected static function collect(Collection $usages, array $target, array $where): void
    {
        $model = $target['model'];

        $model::query()
            ->where(...$where)
            ->limit(self::LIMIT)
            ->get()
            ->each(function (Model $record) use ($usages, $target): void {
                $usages->push([
                    'type' => $target['type'],
                    'field' => $target['field'],
                    'title' => ($target['title'])($record),
                    'url' => ($target['url'])($record),
                ]);
            });
    }

    /**
     * 路径里可能有 _ 或 % 这类 LIKE 通配符，先转义再模糊匹配
     */
    protected static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
