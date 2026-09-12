<?php

namespace App\Filament\Resources\Media\Tables;

use App\Models\Media;
use App\Support\MediaUsage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

use function Filament\Support\generate_icon_html;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('文件名')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (?Media $record): ?string => $record?->name),

                TextColumn::make('human_size')
                    ->label('大小')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('mime_type')
                    ->label('类型')
                    ->toggleable()
                    ->placeholder('-'),

                // 地址列不摆一长串 URL，直接给出看得见的结果：图片显示缩略图，
                // 其它文件显示文件图标，点一下都能在新标签页打开原文件
                TextColumn::make('url')
                    ->label('地址')
                    ->formatStateUsing(fn (Media $record): HtmlString => static::preview($record)),

                TextColumn::make('admin.name')
                    ->label('上传人')
                    ->placeholder('系统')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('usage_count')
                    ->label('引用')
                    ->state(fn (Media $record): int => MediaUsage::count($record))
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? "{$state} 处" : '未引用')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray')
                    ->tooltip('点右侧「引用情况」可以看到具体是哪些内容在用'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('mime_type')
                    ->label('类型')
                    ->options([
                        'image/' => '图片',
                        'application/' => '文档',
                        'video/' => '视频',
                        'audio/' => '音频',
                    ])
                    ->query(fn ($query, array $state) => filled($state['value'] ?? null)
                        ? $query->where('mime_type', 'like', $state['value'] . '%')
                        : $query),
            ])
            ->recordActions([
                Action::make('usages')
                    ->label('引用情况')
                    ->icon(Heroicon::OutlinedLink)
                    ->modalHeading(fn (Media $record): string => '引用情况：' . $record->name)
                    ->modalContent(fn (Media $record): View => view('filament.media-usages', [
                        'usages' => MediaUsage::for($record),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('关闭'),

                EditAction::make(),

                // 删除前把「谁在用这个文件」说清楚，避免误删导致前台图片挂掉
                DeleteAction::make()
                    ->modalDescription(fn (?Media $record): string => MediaUsage::deleteWarning($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription(fn (Collection $records): string => MediaUsage::bulkDeleteWarning($records)),
                ]),
            ])
            ->emptyStateHeading('媒体库为空')
            ->emptyStateDescription('点击右上角「新建」上传第一个文件')
            ->emptyStateIcon('heroicon-o-photo');
    }

    /**
     * 「地址」列的内容：图片直接显示缩略图，其它文件显示文件图标
     *
     * 两种结果都带链接，点开就是文件的原始地址（图片的地址因此依然拿得到）。
     * 这里返回 HtmlString 而不是纯文本，是为了在单元格里直接画出图片 / 图标。
     */
    protected static function preview(Media $record): HtmlString
    {
        $url = $record->url;

        if (blank($url)) {
            return new HtmlString('-');
        }

        $href = e($url);
        $name = e((string) $record->name);
        $linkStyle = 'display: inline-flex; align-items: center; color: inherit;';

        if ($record->isImage()) {
            $imageStyle = 'display: block; height: 40px; width: auto; max-width: 120px;'
                . ' object-fit: cover; border-radius: 6px; border: 1px solid rgba(0, 0, 0, .1);';

            return new HtmlString(
                "<a href=\"{$href}\" target=\"_blank\" rel=\"noopener\" title=\"{$name}\" style=\"{$linkStyle}\">"
                . "<img src=\"{$href}\" alt=\"{$name}\" loading=\"lazy\" style=\"{$imageStyle}\">"
                . '</a>'
            );
        }

        // 非图片文件给一个文件图标，标题里带上文件名，点开即可查看原文件
        $icon = generate_icon_html(Heroicon::OutlinedDocument, size: IconSize::Large)?->toHtml() ?? '';

        return new HtmlString(
            "<a href=\"{$href}\" target=\"_blank\" rel=\"noopener\" title=\"打开文件：{$name}\" style=\"{$linkStyle}\">"
            . $icon
            . '</a>'
        );
    }
}
