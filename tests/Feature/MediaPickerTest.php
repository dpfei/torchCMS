<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\News\Pages\CreateNews;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Media;
use App\Models\Setting;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * 「从媒体库选择」：验证选中的媒体库文件会被回填到图片字段，并且能正常保存。
 */
class MediaPickerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Filament::setCurrentPanel('admin');

        $this->seed(RolePermissionSeeder::class);
        $this->seed(SettingSeeder::class);

        $this->actingAs(Admin::query()->firstOrFail(), 'admin');
    }

    public function test_新闻缩略图可以从媒体库选图并保存(): void
    {
        $media = $this->makeMedia('picked.png');
        $category = Category::query()->create(['cat_name' => '测试栏目']);

        $component = Livewire::test(CreateNews::class)
            ->fillForm([
                'cat_id' => $category->id,
                'title' => '媒体库选图测试',
                'keywords' => '关键词',
                'description' => '描述',
                'content' => '<p>正文</p>',
                'input_time' => now()->toDateTimeString(),
                'copyfrom' => '本站',
                'external_url' => '',
                'status' => 1,
            ])
            ->callAction(
                TestAction::make('pickFromMediaLibrary')->schemaComponent('thumb'),
                ['media_id' => $media->id],
            )
            ->assertHasNoFormErrors();

        // 上传组件的原始状态必须是「文件键 => 路径」的数组，否则上传控件渲染不出来
        $state = $component->get('data.thumb');

        $this->assertIsArray($state);
        $this->assertSame([$media->path], array_values($state));

        $component->call('create')->assertHasNoFormErrors();

        $this->assertDatabaseHas('news', [
            'title' => '媒体库选图测试',
            'thumb' => $media->path,
        ]);
    }

    public function test_站点logo可以从媒体库选图并保存(): void
    {
        $media = $this->makeMedia('logo.png');

        $component = Livewire::test(ManageSettings::class)
            ->callAction(
                TestAction::make('pickFromMediaLibrary')->schemaComponent('site_logo'),
                ['media_id' => $media->id],
            )
            ->assertHasNoFormErrors();

        $this->assertSame([$media->path], array_values($component->get('data.site_logo')));

        $component->call('save');

        $this->assertSame(
            $media->path,
            Setting::query()->where('key', 'site_logo')->value('value'),
        );
    }

    /**
     * 造一条媒体库记录，并在 public 磁盘上放一个真实的 1x1 PNG
     */
    protected function makeMedia(string $filename): Media
    {
        $path = 'media/' . $filename;

        Storage::disk('public')->put($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg=='
        ));

        return Media::query()->create([
            'name' => $filename,
            'path' => $path,
            'disk' => 'public',
        ]);
    }
}
