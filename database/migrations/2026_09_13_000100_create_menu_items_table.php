<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('location', 20)->default('header')->comment('菜单位置：header/footer');
            $table->string('label', 50)->comment('菜单名称');
            $table->string('type', 20)->default('custom')->comment('链接类型：custom/category/page');
            $table->unsignedBigInteger('target_id')->nullable()->comment('指向的栏目或单页 ID');
            $table->string('url', 255)->default('')->comment('自定义链接');
            $table->string('target', 10)->default('_self')->comment('打开方式');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->unsigned()->default(1)->comment('状态');
            $table->timestamps();

            $table->index(['location', 'status', 'sort'], 'location_status_sort');
        });

        $this->importLegacyMenu();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }

    /**
     * 把升级前勾选过「显示在菜单」的栏目与单页搬进菜单表。
     *
     * 菜单接管导航后，栏目与单页上的 is_menu 不再参与渲染，
     * 这里先导入一次，保证升级完前台导航不会突然空掉。
     * 页头原先写死的「首页」也补成一条真实菜单项，之后就能参与排序。
     */
    protected function importLegacyMenu(): void
    {
        foreach (['header', 'footer'] as $location) {
            $sort = 0;

            $rows = [[
                'label' => '首页',
                'type' => 'custom',
                'target_id' => null,
                'url' => '/',
            ]];

            foreach (DB::table('categories')->where('is_menu', 1)->orderBy('sort')->orderBy('id')->get() as $category) {
                $rows[] = [
                    'label' => $category->cat_name,
                    'type' => 'category',
                    'target_id' => $category->id,
                    'url' => '',
                ];
            }

            foreach (DB::table('pages')->where('is_menu', 1)->whereNull('deleted_at')->orderBy('sort')->orderBy('id')->get() as $page) {
                $rows[] = [
                    'label' => $page->title,
                    'type' => 'page',
                    'target_id' => $page->id,
                    'url' => '',
                ];
            }

            foreach ($rows as $row) {
                DB::table('menu_items')->insert($row + [
                    'location' => $location,
                    'target' => '_self',
                    'sort' => $sort++,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
