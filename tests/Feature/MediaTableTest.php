<?php

namespace Tests\Feature;

use App\Filament\Resources\Media\Pages\ListMedia;
use App\Models\Admin;
use App\Models\Media;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * 媒体库列表的「地址」列：不摆一长串 URL，图片直接显示图片，其它文件显示文件图标，
 * 点一下在当前页弹窗预览。
 */
class MediaTableTest extends TestCase
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

    public function test_地址列对图片直接显示图片(): void
    {
        $media = $this->makeMedia('photo.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg=='
        ));

        $html = Livewire::test(ListMedia::class)->html();

        $this->assertTrue($media->isImage());

        // 单元格里直接画出图片，整格可点击（点的是预览动作，不再跳新标签页）
        $cell = $this->cellHtml($html);

        $this->assertStringContainsString('<img src="' . e($media->url) . '" alt="photo.png"', $cell);
        $this->assertStringNotContainsString('<a href=', $cell);
    }

    public function test_点击图片会在当前页弹出放大预览(): void
    {
        $media = $this->makeMedia('photo.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg=='
        ));

        Livewire::test(ListMedia::class)
            ->mountAction(TestAction::make('preview')->table($media))
            ->assertActionMounted(TestAction::make('preview')->table($media))
            // 弹窗里放大显示同一张图，并给出打开原图的入口
            ->assertMountedActionModalSeeHtml('<img src="' . e($media->url) . '"')
            ->assertMountedActionModalSee('在新标签页打开原图');
    }

    public function test_地址列对非图片文件显示文件图标(): void
    {
        $media = $this->makeMedia('report.pdf', '%PDF-1.4 测试文件');

        $html = Livewire::test(ListMedia::class)->html();

        $this->assertFalse($media->isImage());

        // 不是图片：不画 <img>，只给一个文件图标
        $cell = $this->cellHtml($html);

        $this->assertStringNotContainsString('<img', $cell);
        $this->assertStringContainsString('<svg', $cell);
        $this->assertStringContainsString('fi-icon', $cell);
    }

    public function test_点击非图片文件会弹出文件信息(): void
    {
        $media = $this->makeMedia('report.pdf', '%PDF-1.4 测试文件');

        Livewire::test(ListMedia::class)
            ->mountAction(TestAction::make('preview')->table($media))
            ->assertMountedActionModalSee('这个文件不是图片，无法直接预览')
            ->assertMountedActionModalSee('打开文件');
    }

    /**
     * 取出「地址」列那一格的内部 HTML（外面是挂载预览动作的 button）
     */
    protected function cellHtml(string $html): string
    {
        preg_match('/mountTableAction\(&#039;preview&#039;[^>]*>(.*?)<\/button>/s', $html, $matches);

        $this->assertNotEmpty($matches[1] ?? '', '「地址」列没有渲染出可点击的预览单元格');

        return $matches[1];
    }

    /**
     * 造一条媒体库记录，并在 public 磁盘上放一个真实文件
     */
    protected function makeMedia(string $filename, string $contents): Media
    {
        $path = 'media/' . $filename;

        Storage::disk('public')->put($path, $contents);

        return Media::query()->create([
            'name' => $filename,
            'path' => $path,
            'disk' => 'public',
        ]);
    }
}
