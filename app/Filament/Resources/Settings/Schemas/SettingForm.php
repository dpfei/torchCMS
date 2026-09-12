<?php

namespace App\Filament\Resources\Settings\Schemas;

use App\Models\Setting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SettingForm
{
    /**
     * 这两类配置需要专门的控件录入，只能在「系统设置」页里改值，
     * 这里不提供就地编辑，避免把上传控件拍扁成一个文本框。
     */
    protected const TYPES_WITHOUT_INLINE_VALUE = [
        Setting::TYPE_IMAGE,
        Setting::TYPE_SWITCH,
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('配置键')
                    ->required()
                    ->alphaDash()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->helperText('模板中通过 setting(\'配置键\') 读取，仅支持字母、数字、下划线与短横线')
                    ->columnSpanFull(),

                TextInput::make('label')
                    ->label('显示名称')
                    ->required()
                    ->maxLength(50),

                Select::make('type')
                    ->label('输入类型')
                    ->options(Setting::getTypeOptions())
                    ->default(Setting::TYPE_TEXT)
                    ->required()
                    ->selectablePlaceholder(false)
                    ->live(),

                TextInput::make('group')
                    ->label('分组')
                    ->required()
                    ->maxLength(50)
                    ->default('自定义')
                    ->helperText('同一分组的配置会出现在「系统设置」的同一个区块里'),

                TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0)
                    ->helperText('数值越小越靠前'),

                Textarea::make('value')
                    ->label('配置值')
                    ->rows(2)
                    ->visible(fn (Get $get): bool => ! in_array($get('type'), self::TYPES_WITHOUT_INLINE_VALUE, true))
                    ->helperText('图片与开关类型的值，请保存后到「系统设置」中维护')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
