<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Models\Page;
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

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('id'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('标题')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (Page $record): string => $record->title),

                TextColumn::make('slug')
                    ->label('URL 别名')
                    ->copyable()
                    ->url(fn (?Page $record): ?string => $record?->link)
                    ->openUrlInNewTab()
                    ->limit(40)
                    ->tooltip('点击预览前台页面')
                    ->toggleable(isToggledHiddenByDefault: true),

                ImageColumn::make('thumb')
                    ->label(__('thumb'))
                    ->disk('public')
                    ->height(40),

                ToggleColumn::make('status')
                    ->label(__('status')),

                TextColumn::make('sort')
                    ->label(__('sort'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('创建人')
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
            ->defaultSort('sort')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('status'))
                    ->options(Page::getStatusOptions())
                    ->placeholder('全部状态'),

                TrashedFilter::make()
                    ->label('回收站'),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with('user'))
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
                        ->action(fn ($records) => $records->each->update(['status' => Page::STATUS_ENABLED]))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('disable')
                        ->label('批量禁用')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => Page::STATUS_DISABLED]))
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('暂无单页')
            ->emptyStateDescription('点击右上角「新建」添加「关于我们」这类不进栏目的独立页面')
            ->emptyStateIcon('heroicon-o-document');
    }
}
