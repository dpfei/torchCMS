<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('id'),

                Select::make('parent_id')
                    ->label('父级栏目')
                    ->options(Category::getOptionList())
                    ->placeholder('顶级栏目')
                    ->searchable(),

                TextInput::make('cat_name')
                    ->label('栏目名称')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('thumb')
                    ->label('缩略图')
                    ->image()
                    ->directory('categories')
                    ->maxSize(1024),

                Textarea::make('description')
                    ->label('描述')
                    ->rows(3)
                    ->maxLength(500),

                TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_menu')
                    ->label('是否显示在菜单')
                    ->default(true),
            ]);
    }
}
