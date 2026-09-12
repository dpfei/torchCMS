<?php

namespace Tests\Feature;

use App\Filament\Resources\Media\Pages\ListMedia;
use App\Models\Admin;
use App\Models\Media;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * 媒体库列表的「地址」列：不再摆一长串 URL，图片直接显示图片，其它文件显示可点击的文件图标。
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

        // 单元格里直接画出图片，并且点一下能在新标签页打开原文件
        $this->assertStringContainsString('<a href="' . e($media->url) . '" target="_blank"', $html);
        $this->assertStringContainsString('<img src="' . e($media->url) . '" alt="photo.png"', $html);
    }

    public function test_地址列对非图片文件显示可点击的文件图标(): void
    {
        $media = $this->makeMedia('report.pdf', '%PDF-1.4 测试文件');

        $html = Livewire::test(ListMedia::class)->html();

        $this->assertFalse($media->isImage());

        // 不是图片：不画 <img>，只给一个指向原文件的图标链接
        $this->assertStringNotContainsString('<img src="' . e($media->url) . '"', $html);
        $this->assertStringContainsString('href="' . e($media->url) . '" target="_blank"', $html);
        $this->assertStringContainsString('title="打开文件：report.pdf"', $html);

        // 链接里确实画出了文件图标（svg），而不是空链接
        preg_match('/title="打开文件：report\.pdf"[^>]*>(.*?)<\/a>/s', $html, $matches);
        $inner = $matches[1] ?? '';

        $this->assertStringContainsString('<svg', $inner);
        $this->assertStringContainsString('fi-icon', $inner);
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
