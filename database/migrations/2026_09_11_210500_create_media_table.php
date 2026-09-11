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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->default('')->comment('文件名');
            $table->string('path', 255)->comment('存储路径');
            $table->string('disk', 50)->default('public')->comment('存储磁盘');
            $table->string('mime_type', 100)->nullable()->comment('MIME 类型');
            $table->unsignedInteger('size')->default(0)->comment('文件大小(字节)');
            $table->string('extension', 20)->nullable()->comment('扩展名');
            $table->unsignedBigInteger('admin_id')->nullable()->comment('上传人');
            $table->timestamps();

            $table->index('admin_id');
            $table->index('mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
