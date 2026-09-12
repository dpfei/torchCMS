<?php

namespace App\Filament\Resources\MenuItems\Tables;

use App\Models\MenuItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('菜单名称')
                    ->searchable(),

                TextColumn::make('location')
                    ->label('位置')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => MenuItem::getLocationOptions()[$state] ?? $state),

                TextColumn::make('type')
                    ->label('链接类型')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => MenuItem::getTypeOptions()[$state] ?? $state),

                TextColumn::make('link')
                    ->label('链接地址')
                    ->limit(50)
                    ->url(fn (MenuItem $record): ?string => $record->link === '#' ? null : $record->link)
                    ->openUrlInNewTab()
                    ->tooltip('点击打开'),

                TextColumn::make('target')
                    ->label('打开方式')
                    ->formatStateUsing(fn (string $state): string => MenuItem::getTargetOptions()[$state] ?? $state)
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('status')
                    ->label(__('status')),

                TextColumn::make('sort')
                    ->label(__('sort')),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->paginated(false)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['category', 'page']))
            ->filters([
                SelectFilter::make('location')
                    ->label('菜单位置')
                    ->options(MenuItem::getLocationOptions())
                    ->default(MenuItem::LOCATION_HEADER),

                SelectFilter::make('status')
                    ->label(__('status'))
                    ->options(MenuItem::getStatusOptions()),
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
            ->emptyStateHeading('该位置还没有菜单项')
            ->emptyStateDescription('可以把栏目、单页挂上来，也可以直接添加一个外部链接')
            ->emptyStateIcon('heroicon-o-bars-3');
    }
}
