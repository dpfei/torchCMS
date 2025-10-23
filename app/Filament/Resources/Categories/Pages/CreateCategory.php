<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction()->label('取消'),
            $this->getCreateAnotherFormAction()->label('保存并继续新建'),
            $this->getCreateFormAction()->label('保存'),
        ];
    }
}
