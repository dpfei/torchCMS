<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * 内置设置项：key => [显示名称, 默认值, 输入类型, 分组]
     *
     * 可反复执行：已存在的配置只同步元数据（名称/类型/分组/排序），
     * 保留站长在后台填写过的值。
     *
     * @return array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    protected function definitions(): array
    {
        return [
            'site_name' => ['站点名称', 'torchCMS', Setting::TYPE_TEXT, '基本设置'],
            'site_url' => ['站点地址', config('app.url'), Setting::TYPE_URL, '基本设置'],
            'site_logo' => ['站点 Logo', '', Setting::TYPE_IMAGE, '基本设置'],
            'site_keywords' => ['SEO 关键词', '', Setting::TYPE_TEXT, '基本设置'],
            'site_description' => ['站点描述', '基于 Laravel 与 Filament 构建的内容管理系统', Setting::TYPE_TEXTAREA, '基本设置'],
            'contact_email' => ['联系邮箱', '', Setting::TYPE_EMAIL, '联系方式'],
            'contact_phone' => ['联系电话', '', Setting::TYPE_TEXT, '联系方式'],
            'icp' => ['ICP 备案号', '', Setting::TYPE_TEXT, '联系方式'],
            'copyright' => ['版权信息', '© ' . date('Y') . ' torchCMS. All rights reserved.', Setting::TYPE_TEXT, '联系方式'],
        ];
    }

    public function run(): void
    {
        $sort = 0;

        foreach ($this->definitions() as $key => [$label, $value, $type, $group]) {
            $metadata = [
                'label' => $label,
                'type' => $type,
                'group' => $group,
                'sort' => $sort++,
            ];

            $setting = Setting::query()->firstOrCreate(
                ['key' => $key],
                $metadata + ['value' => $value]
            );

            if (! $setting->wasRecentlyCreated) {
                $setting->update($metadata);
            }
        }
    }
}
