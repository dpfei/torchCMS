<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 单页：不归属栏目、不参与列表聚合、没有发布时间的内容，
     * 例如「关于我们」「联系方式」「服务条款」。
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->mediumInteger('id', true)->unsigned()->comment('单页ID');
            $table->string('title', 80)->default('')->comment('标题');
            $table->string('slug', 191)->nullable()->comment('URL 别名');
            $table->string('thumb', 100)->default('')->comment('封面图');
            $table->char('keywords', 40)->default('')->comment('关键词');
            $table->mediumText('description')->nullable()->comment('描述');
            $table->mediumText('content')->nullable()->comment('内容');
            $table->tinyInteger('sort')->unsigned()->default(0)->comment('排序');
            $table->tinyInteger('is_menu')->unsigned()->default(0)->comment('是否显示在菜单');
            $table->tinyInteger('status')->unsigned()->default(1)->comment('状态');
            $table->integer('user_id')->nullable()->comment('创建人');
            $table->datetime('created_at')->nullable()->comment('创建时间');
            $table->datetime('updated_at')->nullable()->comment('编辑时间');
            $table->datetime('deleted_at')->nullable()->comment('删除时间');

            // 主键与索引
            $table->primary('id');
            $table->unique('slug');
            $table->index(['status', 'is_menu', 'sort'], 'menu')->comment('菜单取值索引');

            // 设置引擎和字符集
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 表备注
            $table->comment('单页表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
