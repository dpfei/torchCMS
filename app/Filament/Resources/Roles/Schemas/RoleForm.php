<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Permission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('角色标识')
                    ->required()
                    ->alphaDash()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->helperText('英文标识，例如 editor、auditor'),

                CheckboxList::make('permissions')
                    ->label('权限')
                    ->relationship('permissions', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn (Permission $record): string => Permission::labelFor($record->name)
                    )
                    ->columns(3)
                    ->searchable()
                    ->bulkToggleable()
                    ->columnSpanFull(),
            ]);
    }
}
