<?php

namespace App\Filament\Resources\News\Tables;

use App\Models\Category;
use App\Models\News;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('id'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('category.cat_name')
                    ->label(__('news.cat_id'))
                    ->placeholder('未分类')
                    ->sortable(),

                TextColumn::make('title')
                    ->label(__('news.title'))
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (News $record): string => $record->title),

                TextColumn::make('slug')
                    ->label('URL 别名')
                    ->copyable()
                    ->url(fn (?News $record): ?string => $record ? route('news.show', $record) : null)
                    ->openUrlInNewTab()
                    ->limit(40)
                    ->tooltip('点击预览前台页面')
                    ->toggleable(isToggledHiddenByDefault: true),

                ImageColumn::make('thumb')
                    ->label(__('thumb'))
                    ->disk('public')
                    ->height(40),

                TextColumn::make('sort')
                    ->label(__('sort'))
                    ->numeric()
                    ->sortable(),

                ToggleColumn::make('status')
                    ->label(__('status')),

                TextColumn::make('input_time')
                    ->label(__('news.input_time'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('readpoint')
                    ->label(__('news.readpoint'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label(__('news.user_id'))
                    ->placeholder('系统')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('updated_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('删除时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('cat_id')
                    ->label(__('news.cat_id'))
                    ->options(Category::getOptionList())
                    ->placeholder('全部栏目'),

                SelectFilter::make('status')
                    ->label(__('status'))
                    ->options(News::getStatusOptions())
                    ->placeholder('全部状态'),

                TrashedFilter::make()
                    ->label('回收站'),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'user']))
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('enable')
                        ->label('批量启用')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => News::STATUS_ENABLED]))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('disable')
                        ->label('批量禁用')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => News::STATUS_DISABLED]))
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('暂无内容')
            ->emptyStateDescription('点击右上角「新建」发布第一篇内容')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
