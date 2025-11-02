<?php

namespace App\Filament\Resources\News\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('cat_id')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('title')
                    ->required(),
                TextInput::make('thumb')
                    ->required(),
                TextInput::make('keywords')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('external_url')
                    ->url()
                    ->required(),
                TextInput::make('sort')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('input_time')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('readpoint')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('copyfrom')
                    ->required(),
                TextInput::make('user_id')
                    ->numeric(),
            ]);
    }
}
