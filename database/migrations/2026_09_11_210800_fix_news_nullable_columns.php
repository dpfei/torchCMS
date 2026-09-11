<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 原始表结构中 external_url / description / content 均为 NOT NULL 且无默认值，
     * 在 MySQL 严格模式下未赋值会导致插入失败，这里修正为可选字段。
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->char('external_url', 100)->default('')->comment('外部链接')->change();
            $table->mediumText('description')->nullable()->comment('描述')->change();
            $table->mediumText('content')->nullable()->comment('内容')->change();
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->char('external_url', 100)->comment('外部链接')->change();
            $table->mediumText('description')->comment('描述')->change();
            $table->mediumText('content')->comment('内容')->change();
        });
    }
};
