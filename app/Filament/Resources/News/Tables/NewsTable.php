<?php

namespace App\Filament\Resources\News\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                TextColumn::make('cat_id')
                    ->label(__('news.cat_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('news.title'))
                    ->searchable(),
                TextColumn::make('thumb')
                    ->label(__('thumb'))
                    ->searchable(),
                TextColumn::make('keywords')
                    ->label(__('keywords'))
                    ->searchable(),
                TextColumn::make('external_url')
                    ->label(__('news.external_url'))
                    ->searchable(),
                TextColumn::make('sort')
                    ->label(__('sort'))
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('status')
                    ->label(__('status')),
                TextColumn::make('input_time')
                    ->label(__('news.input_time'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('readpoint')
                    ->label(__('news.readpoint'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('copyfrom')
                    ->label(__('news.copyfrom'))
                    ->searchable(),
                TextColumn::make('user_id')
                    ->label(__('news.user_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('updated_at'))
                    ->dateTime()
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
