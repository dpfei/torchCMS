<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected static ?string $breadcrumb = '列表';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新建'),
        ];
    }
}
