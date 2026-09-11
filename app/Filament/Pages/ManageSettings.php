<?php

namespace App\Filament\Pages;

use App\Models\Admin;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = '系统管理';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = '系统设置';

    protected static ?string $title = '系统设置';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof Admin && $user->can('view_setting');
    }

    public function mount(): void
    {
        $this->form->fill(Setting::allAsArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('基础信息')
                    ->description('站点名称、Logo 以及 SEO 相关信息')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('站点名称')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('site_url')
                            ->label('站点地址')
                            ->url()
                            ->maxLength(255),

                        FileUpload::make('site_logo')
                            ->label('站点 Logo')
                            ->image()
                            ->disk('public')
                            ->directory('settings')
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        TextInput::make('site_keywords')
                            ->label('SEO 关键词')
                            ->maxLength(255)
                            ->helperText('多个关键词用英文逗号分隔'),

                        TextInput::make('contact_email')
                            ->label('联系邮箱')
                            ->email()
                            ->maxLength(255),

                        Textarea::make('site_description')
                            ->label('站点描述')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('联系方式与备案')
                    ->schema([
                        TextInput::make('contact_phone')
                            ->label('联系电话')
                            ->maxLength(50),

                        TextInput::make('icp')
                            ->label('ICP 备案号')
                            ->maxLength(100),

                        TextInput::make('copyright')
                            ->label('版权信息')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            Setting::set($key, is_array($value) ? ($value[0] ?? null) : $value);
        }

        Notification::make()
            ->title('设置已保存')
            ->success()
            ->send();
    }

    /**
     * @return array<int, Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('保存设置')
                ->submit('save'),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
                EmbeddedSchema::make('form'),
            ])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make($this->getFormActions()),
                ]),
        ]);
    }
}
