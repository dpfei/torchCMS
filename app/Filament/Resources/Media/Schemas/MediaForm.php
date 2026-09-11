<?php

namespace App\Filament\Resources\Media\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('name'),

                FileUpload::make('path')
                    ->label('文件')
                    ->disk('public')
                    ->directory('media')
                    ->storeFileNamesIn('name')
                    ->maxSize(10240)
                    ->openable()
                    ->downloadable()
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
