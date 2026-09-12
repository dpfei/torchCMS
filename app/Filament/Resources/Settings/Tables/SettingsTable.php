<?php

namespace App\Filament\Resources\Settings\Tables;

use App\Models\Setting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('显示名称')
                    ->description(fn (Setting $record): string => $record->key)
                    ->searchable(['label', 'key']),

                TextColumn::make('value')
                    ->label('配置值')
                    ->limit(40)
                    ->placeholder('—')
                    ->tooltip(fn (Setting $record): ?string => $record->value ?: null),

                TextColumn::make('type')
                    ->label('输入类型')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Setting::getTypeOptions()[$state] ?? $state),

                TextColumn::make('group')
                    ->label('分组')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('sort')
                    ->label(__('sort'))
                    ->sortable(),
            ])
            ->defaultSort('sort')
            ->filters([
                SelectFilter::make('group')
                    ->label('分组')
                    ->options(fn (): array => Setting::query()
                        ->distinct()
                        ->orderBy('group')
                        ->pluck('group', 'group')
                        ->toArray())
                    ->placeholder('全部分组'),
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
            ->emptyStateHeading('还没有自定义设置项')
            ->emptyStateDescription('新增后无需改代码，模板里用 setting(\'配置键\') 就能读到')
            ->emptyStateIcon('heroicon-o-adjustments-horizontal');
    }
}
