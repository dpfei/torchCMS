<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MenuItemForm
{
    /**
     * 需要指向具体内容的链接类型
     */
    protected const TYPES_WITH_TARGET = [
        MenuItem::TYPE_CATEGORY,
        MenuItem::TYPE_PAGE,
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('location')
                    ->label('菜单位置')
                    ->options(MenuItem::getLocationOptions())
                    ->default(MenuItem::LOCATION_HEADER)
                    ->required()
                    ->selectablePlaceholder(false),

                TextInput::make('label')
                    ->label('菜单名称')
                    ->required()
                    ->maxLength(50),

                Select::make('type')
                    ->label('链接类型')
                    ->options(MenuItem::getTypeOptions())
                    ->default(MenuItem::TYPE_CUSTOM)
                    ->required()
                    ->selectablePlaceholder(false)
                    ->live(),

                Select::make('target_id')
                    ->label('目标内容')
                    ->options(fn (Get $get): array => match ($get('type')) {
                        MenuItem::TYPE_CATEGORY => Category::getOptionList(),
                        MenuItem::TYPE_PAGE => Page::query()
                            ->orderBy('sort')
                            ->orderBy('id')
                            ->pluck('title', 'id')
                            ->toArray(),
                        default => [],
                    })
                    ->searchable()
                    ->visible(fn (Get $get): bool => in_array($get('type'), self::TYPES_WITH_TARGET, true))
                    ->required(fn (Get $get): bool => in_array($get('type'), self::TYPES_WITH_TARGET, true))
                    ->helperText('保存的是内容引用，栏目或单页改了别名，菜单链接会自动跟着变'),

                TextInput::make('url')
                    ->label('链接地址')
                    ->maxLength(255)
                    ->visible(fn (Get $get): bool => $get('type') === MenuItem::TYPE_CUSTOM)
                    ->required(fn (Get $get): bool => $get('type') === MenuItem::TYPE_CUSTOM)
                    ->placeholder('/about 或 https://github.com/xxx')
                    ->helperText('站内路径可以省略开头的斜杠，外部地址请带上 http(s)://'),

                Select::make('target')
                    ->label('打开方式')
                    ->options(MenuItem::getTargetOptions())
                    ->default(MenuItem::TARGET_SELF)
                    ->selectablePlaceholder(false),

                TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0)
                    ->helperText('数值越小越靠前；列表里拖拽排序时只影响当前筛选出的位置'),

                Toggle::make('status')
                    ->label('启用')
                    ->default(true),
            ])
            ->columns(2);
    }
}
