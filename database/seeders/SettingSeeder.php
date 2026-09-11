<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name' => 'torchCMS',
            'site_url' => config('app.url'),
            'site_logo' => '',
            'site_keywords' => '',
            'site_description' => '基于 Laravel 与 Filament 构建的内容管理系统',
            'contact_email' => '',
            'contact_phone' => '',
            'icp' => '',
            'copyright' => '© ' . date('Y') . ' torchCMS. All rights reserved.',
        ];

        foreach ($defaults as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
