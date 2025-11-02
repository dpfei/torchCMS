<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->integer('id', true)->unsigned()->comment('分类ID');
            $table->integer('parent_id')->unsigned()->nullable()->comment('上级栏目分类');
            $table->string('cat_name', 100)->nullable()->comment('分类名称');
            $table->string('thumb', 100)->nullable()->comment('封面图');
            $table->mediumText('description')->nullable()->comment('描述');
            $table->integer('sort')->unsigned()->nullable()->comment('排序');
            $table->tinyInteger('is_menu')->unsigned()->nullable()->comment('是否是菜单');
            $table->datetime('created_at')->nullable()->comment('创建时间');
            $table->datetime('updated_at')->nullable()->comment('编辑时间');
            $table->datetime('deleted_at')->nullable()->comment('删除时间');
            
            // 主键
            $table->primary('id');
            
            // 设置引擎和字符集
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            // 表备注
            $table->comment('新闻分类表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};