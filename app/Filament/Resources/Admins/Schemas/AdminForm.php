<?php

namespace App\Filament\Resources\Admins\Schemas;

use App\Models\Role;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('姓名')
                    ->required()
                    ->maxLength(50),

                TextInput::make('email')
                    ->label('邮箱')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('密码')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->maxLength(255)
                    ->helperText('留空则不修改密码'),

                CheckboxList::make('roles')
                    ->label('角色')
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn (Role $record): string => $record->label
                    )
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
