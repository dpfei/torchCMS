<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 内置设置项的元数据：key => [显示名称, 输入类型, 分组]
     *
     * 这份定义在迁移里内联了一份，是为了让已经装好的站点只跑 migrate 就能补齐元数据，
     * 不必依赖重新执行 Seeder（Seeder 里维护的是新站点的初始数据）。
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    protected array $builtins = [
        'site_name' => ['站点名称', 'text', '基本设置'],
        'site_url' => ['站点地址', 'url', '基本设置'],
        'site_logo' => ['站点 Logo', 'image', '基本设置'],
        'site_keywords' => ['SEO 关键词', 'text', '基本设置'],
        'site_description' => ['站点描述', 'textarea', '基本设置'],
        'contact_email' => ['联系邮箱', 'email', '联系方式'],
        'contact_phone' => ['联系电话', 'text', '联系方式'],
        'icp' => ['ICP 备案号', 'text', '联系方式'],
        'copyright' => ['版权信息', 'text', '联系方式'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('label', 50)->default('')->after('key')->comment('显示名称');
            $table->string('type', 20)->default('text')->after('value')->comment('输入类型');
            $table->integer('sort')->default(0)->after('group')->comment('排序');
        });

        $sort = 0;

        foreach ($this->builtins as $key => [$label, $type, $group]) {
            DB::table('settings')->where('key', $key)->update([
                'label' => $label,
                'type' => $type,
                'group' => $group,
                'sort' => $sort++,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['label', 'type', 'sort']);
        });
    }
};
