<?php

namespace App\Filament\Resources\Media\Tables;

use App\Models\Media;
use App\Support\MediaUsage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('url')
                    ->label('预览')
                    ->height(40)
                    ->visible(fn (?Media $record): bool => is_null($record) || str_starts_with((string) $record->mime_type, 'image/')),

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

                TextColumn::make('url')
                    ->label('地址')
                    ->copyable()
                    ->limit(40)
                    ->url(fn (?Media $record): ?string => $record?->url, shouldOpenInNewTab: true),

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
}
