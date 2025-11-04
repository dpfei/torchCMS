<?php

namespace App\Filament\Resources\News\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("id")
                    ->label(__('id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category.cat_name')
                    ->label(__('news.cat_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('news.title'))
                    ->searchable(),
                ImageColumn::make('thumb')
                    ->label(__('thumb'))
                    ->searchable(),
                TextColumn::make('sort')
                    ->label(__('sort'))
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('status')
                    ->label(__('status')),
                TextColumn::make('input_time')
                    ->label(__('news.input_time'))
                    ->sortable(),
                TextColumn::make('readpoint')
                    ->label(__('news.readpoint'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user_id')
                    ->label(__('news.user_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('updated_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
