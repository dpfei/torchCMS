<?php

namespace Tests\Feature;

use App\Filament\Resources\News\Pages\CreateNews;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Media;
use App\Models\News;
use App\Models\Page;
use App\Models\Setting;
use App\Support\MediaLibrary;
use App\Support\MediaUsage;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * 媒体库四条增强：上传即入库、弹窗内直接上传、被谁引用反查、路径防篡改白名单。
 */
class MediaLibraryEnhancementsTest extends TestCase
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

    /**
     * B：在各处图片字段上传后，文件会自动登记到媒体库
     */
    public function test_图片字段上传后自动登记进媒体库(): void
    {
        $category = Category::query()->create(['cat_name' => '测试栏目']);

        Livewire::test(CreateNews::class)
            ->fillForm([
                'cat_id' => $category->id,
                'title' => '上传入库测试',
                'keywords' => '关键词',
                'description' => '描述',
                'content' => '<p>正文</p>',
                'input_time' => now()->toDateTimeString(),
                'copyfrom' => '本站',
                'external_url' => '',
                'status' => 1,
                'thumb' => UploadedFile::fake()->image('uploaded.png'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $thumb = News::query()->where('title', '上传入库测试')->value('thumb');

        $this->assertNotNull($thumb);
        $this->assertStringStartsWith('news/', $thumb);
        $this->assertTrue(Storage::disk('public')->exists($thumb));

        // 媒体库里应该正好有这条记录
        $this->assertDatabaseHas('media', [
            'path' => $thumb,
            'disk' => 'public',
        ]);
    }

    /**
     * 弹窗内为空媒体库补一张：上传的文件入库并直接回填到字段
     */
    public function test_选图弹窗内上传的图片会入库并回填字段(): void
    {
        $category = Category::query()->create(['cat_name' => '测试栏目']);

        $component = Livewire::test(CreateNews::class)
            ->fillForm([
                'cat_id' => $category->id,
                'title' => '弹窗上传测试',
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
                ['upload' => UploadedFile::fake()->image('picked-upload.png')],
            )
            ->assertHasNoFormErrors();

        $state = $component->get('data.thumb');

        $this->assertIsArray($state);

        $path = array_values($state)[0] ?? null;

        $this->assertNotNull($path);
        $this->assertStringStartsWith('media/', $path);
        $this->assertTrue(Storage::disk('public')->exists($path));

        // 上传的文件已经入库
        $this->assertDatabaseHas('media', [
            'path' => $path,
            'disk' => 'public',
        ]);
    }

    /**
     * 反向查出「这个文件被哪些内容用了」，删除前给出预警
     */
    public function test_媒体库反查内容引用并按引用情况预警(): void
    {
        $media = $this->makeMedia('used.png');
        $path = $media->path;

        News::query()->create(['title' => '引用封面的新闻', 'thumb' => $path]);
        News::query()->create(['title' => '正文嵌图的新闻', 'content' => '<img src="/storage/' . $path . '">']);
        Page::query()->create(['title' => '引用封面的单页', 'thumb' => $path]);
        Category::query()->create(['cat_name' => '引用缩略图的栏目', 'thumb' => $path]);
        Setting::query()->create(['key' => 'banner', 'label' => '首页横幅', 'value' => $path, 'type' => Setting::TYPE_IMAGE]);

        $usages = MediaUsage::for($media);

        $this->assertSame(5, MediaUsage::count($media));
        $this->assertEqualsCanonicalizing(
            ['新闻', '新闻', '单页', '栏目', '系统设置'],
            $usages->pluck('type')->all(),
        );

        $warning = MediaUsage::deleteWarning($media);

        $this->assertStringContainsString('5 处内容', $warning);
        $this->assertStringContainsString('引用封面的新闻', $warning);

        // 没被引用的文件要明确提示可以安全删除
        $unused = $this->makeMedia('unused.png');

        $this->assertSame(0, MediaUsage::count($unused));
        $this->assertStringContainsString('没有被任何内容引用', MediaUsage::deleteWarning($unused));
    }

    /**
     * 路径防篡改白名单：只放行图片目录里真实存在的文件
     */
    public function test_路径白名单只放行图片目录里真实存在的文件(): void
    {
        $media = $this->makeMedia('ok.png');

        // 白名单目录里真实存在的文件 → 放行（媒体库引用、历史上传都靠这条）
        $this->assertTrue(MediaLibrary::isAllowedPath($media->path));

        // 白名单目录但文件不存在 → 拒绝
        $this->assertFalse(MediaLibrary::isAllowedPath('media/missing.png'));

        // 不在白名单目录 → 拒绝
        $this->assertFalse(MediaLibrary::isAllowedPath('uploads/evil.png'));

        // 目录穿越 → 拒绝
        $this->assertFalse(MediaLibrary::isAllowedPath('media/../secret.png'));
        $this->assertFalse(MediaLibrary::isAllowedPath(''));

        // 不存在的路径不会被登记进媒体库
        $this->assertNull(MediaLibrary::index('media/missing.png'));
        $this->assertDatabaseMissing('media', ['path' => 'media/missing.png']);
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
