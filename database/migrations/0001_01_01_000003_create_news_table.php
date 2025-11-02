<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->mediumInteger('id', true)->unsigned()->comment('新闻ID');
            $table->smallInteger('cat_id')->unsigned()->default(0)->comment('分类ID');
            $table->string('title', 80)->default('')->comment('标题');
            $table->string('thumb', 100)->default('')->comment('缩略图');
            $table->char('keywords', 40)->default('')->comment('关键词');
            $table->mediumText('description')->comment('描述');
            $table->char('external_url', 100)->comment('外部链接');
            $table->tinyInteger('sort')->unsigned()->default(0)->comment('排序');
            $table->tinyInteger('status')->unsigned()->default(1)->comment('状态');
            $table->integer('input_time')->unsigned()->default(0)->comment('录入时间');
            $table->mediumText('content')->comment('内容');
            $table->smallInteger('readpoint')->unsigned()->default(0)->comment('阅读点数');
            $table->string('copyfrom', 100)->default('')->comment('来源');
            $table->integer('user_id')->nullable()->comment('创建人');
            $table->datetime('created_at')->nullable()->comment('创建时间');
            $table->datetime('updated_at')->nullable()->comment('更新时间');

            // 索引
            $table->primary('id')->comment('主键索引');
            $table->index(['status', 'sort', 'id'], 'status')->comment('状态排序索引');
            $table->index(['cat_id', 'status', 'sort', 'id'], 'listorder')->comment('分类排序索引');
            $table->index(['cat_id', 'status', 'id'], 'catid')->comment('分类状态索引');

            // 设置引擎和字符集
            $table->engine = 'MyISAM';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
