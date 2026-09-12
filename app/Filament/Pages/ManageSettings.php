<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Components\MediaLibraryFileUpload;
use App\Filament\Resources\Settings\SettingResource;
use App\Models\Admin;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * 系统设置：按分组编辑各个设置项的值。
 *
 * 表单是按 settings 表动态渲染的，新增配置项不需要再改这个文件；
 * 「有哪些配置项」则由「设置项管理」维护。
 */
class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = '系统管理';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = '系统设置';

    protected static ?string $title = '系统设置';

    protected ?string $subheading = '新增或删除配置项，请前往「设置项管理」';

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
        $sections = Setting::grouped()
            ->map(fn (Collection $items, string $group): Section => Section::make($group !== '' ? $group : '其他设置')
                ->schema($items->map(fn (Setting $setting): Component => $this->fieldFor($setting))->all())
                ->columns(2))
            ->values()
            ->all();

        return $schema
            ->components($sections)
            ->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            Setting::set($key, $this->normalizeValue($value));
        }

        Notification::make()
            ->title('设置已保存')
            ->success()
            ->send();
    }

    /**
     * 按设置项的输入类型生成对应控件
     */
    protected function fieldFor(Setting $setting): Component
    {
        $label = $setting->label !== '' ? $setting->label : $setting->key;

        return match ($setting->type) {
            Setting::TYPE_TEXTAREA => Textarea::make($setting->key)
                ->label($label)
                ->rows(3),

            Setting::TYPE_IMAGE => MediaLibraryFileUpload::make($setting->key)
                ->label($label)
                ->image()
                ->disk('public')
                ->directory('settings')
                ->maxSize(2048),

            Setting::TYPE_SWITCH => Toggle::make($setting->key)
                ->label($label),

            Setting::TYPE_EMAIL => TextInput::make($setting->key)
                ->label($label)
                ->email(),

            Setting::TYPE_URL => TextInput::make($setting->key)
                ->label($label)
                ->url(),

            default => TextInput::make($setting->key)
                ->label($label),
        };
    }

    /**
     * 表单值与存储值对齐：开关存 1/0，上传控件只取第一个文件
     */
    protected function normalizeValue(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            return $value[0] ?? '';
        }

        return $value ?? '';
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

            Action::make('manage')
                ->label('管理设置项')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->url(fn (): string => SettingResource::getUrl())
                ->visible(fn (): bool => SettingResource::canViewAny()),
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
