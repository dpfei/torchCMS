<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Forms\Components\MediaLibraryFileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('标题')
                    ->required()
                    ->maxLength(80),

                TextInput::make('slug')
                    ->label('URL 别名')
                    ->maxLength(191)
                    ->unique(ignoreRecord: true)
                    ->helperText('留空将根据标题自动生成（中文自动转拼音），前台地址形如 /about')
                    ->columnSpanFull(),

                MediaLibraryFileUpload::make('thumb')
                    ->label('封面图')
                    ->image()
                    ->disk('public')
                    ->directory('pages')
                    ->maxSize(2048),

                TextInput::make('keywords')
                    ->label('SEO 关键词')
                    ->maxLength(40)
                    ->helperText('多个关键词用英文逗号分隔'),

                Textarea::make('description')
                    ->label('描述')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('用于 SEO 描述，留空则自动截取正文')
                    ->columnSpanFull(),

                RichEditor::make('content')
                    ->label('内容')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0)
                    ->helperText('数值越小越靠前'),

                Toggle::make('status')
                    ->label('启用')
                    ->default(true),
            ]);
    }
}
