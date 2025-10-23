<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cat_name')
                    ->label('栏目名称')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('parent.cat_name')
                    ->label('父级栏目')
                    ->sortable()
                    ->placeholder('顶级栏目'),

                ImageColumn::make('thumb')
                    ->label('缩略图'),

                TextColumn::make('description')
                    ->label('描述')
                    ->limit(50),

                TextColumn::make('sort')
                    ->label('排序')
                    ->sortable(),

                BooleanColumn::make('is_menu')
                    ->label('是否显示在菜单')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label('父级栏目')
                    ->options(Category::getOptionList())
                    ->placeholder('全部分类'),

                SelectFilter::make('is_menu')
                    ->label('是否显示在菜单')
                    ->options([
                        1 => '是',
                        0 => '否',
                    ])
                    ->placeholder('全部'),
            ])
            ->recordActions([
                EditAction::make()->label('编辑'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('没有栏目数据')
            ->emptyStateDescription('创建您的第一个栏目')
            ->emptyStateIcon('heroicon-o-rectangle-stack');
    }
}
