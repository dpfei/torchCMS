<?php

use App\Support\SlugGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 为内容与栏目增加 URL 别名（伪静态），并回填历史数据。
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->after('title')->comment('URL 别名');
            $table->unique('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->after('cat_name')->comment('URL 别名');
            $table->unique('slug');
        });

        $this->backfill('news', 'title', 'news');
        $this->backfill('categories', 'cat_name', 'category');
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }

    /**
     * 为历史数据回填 slug
     */
    protected function backfill(string $table, string $sourceColumn, string $prefix): void
    {
        $taken = [];

        $rows = DB::table($table)->orderBy('id')->get(['id', $sourceColumn]);

        foreach ($rows as $row) {
            $base = SlugGenerator::normalize((string) ($row->{$sourceColumn} ?? ''));

            if ($base === '') {
                $base = $prefix . '-' . $row->id;
            }

            $base = SlugGenerator::trim($base);

            $slug = SlugGenerator::unique($base, fn (string $candidate): bool => isset($taken[$candidate]));

            $taken[$slug] = true;

            DB::table($table)->where('id', $row->id)->update(['slug' => $slug]);
        }
    }
};
