<?php

namespace App\Filament\Resources\Media\Tables;

use App\Models\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('媒体库为空')
            ->emptyStateDescription('点击右上角「新建」上传第一个文件')
            ->emptyStateIcon('heroicon-o-photo');
    }
}
