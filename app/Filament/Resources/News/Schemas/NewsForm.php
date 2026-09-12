<?php

namespace App\Filament\Resources\News\Schemas;

use App\Filament\Actions\PickFromMediaLibraryAction;
use App\Models\Category;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cat_id')
                    ->label(__('news.cat_id'))
                    ->options(Category::getOptionList())
                    ->required(),
                TextInput::make('title')
                    ->label(__('news.title'))
                    ->required(),
                TextInput::make('slug')
                    ->label('URL 别名')
                    ->maxLength(191)
                    ->unique(ignoreRecord: true)
                    ->helperText('留空将根据标题自动生成（中文自动转拼音），前台地址形如 /news/别名')
                    ->columnSpanFull(),
                FileUpload::make('thumb')
                    ->label(__('thumb'))
                    ->image()
                    ->disk('public')
                    ->directory('news')
                    ->maxSize(2048)
                    ->required()
                    ->hintAction(PickFromMediaLibraryAction::hint()),
                TextInput::make('keywords')
                    ->label(__('keywords'))
                    ->required(),
                Textarea::make('description')
                    ->label(__('description'))
                    ->rows(5)
                    ->required()
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->label(__('news.content'))
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('input_time')
                    ->label(__('news.input_time'))
                    ->default(now())
                    ->required(),
                TextInput::make('copyfrom')
                    ->label(__('news.copyfrom'))
                    ->required(),
                TextInput::make('external_url')
                    ->label(__('news.external_url'))
                    ->url(),
                TextInput::make('sort')
                    ->label(__('sort'))
                    ->numeric()
                    ->default(0),
                Toggle::make('status')
                    ->label(__('status'))
                    ->required()
                    ->default(1),
            ]);
    }
}
