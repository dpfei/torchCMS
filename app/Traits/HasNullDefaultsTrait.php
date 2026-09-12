<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * 在写入前把空值补齐，避免显式写入 NULL 撞上「列不允许为 NULL」。
 *
 * 库里有一批沿用老站结构的列是 NOT NULL + DEFAULT ''（排序类字段是 DEFAULT 0），
 * 而列默认值只在 INSERT 语句里「不含该列」时才会生效：表单里的可选字段留空时
 * 提交的是 null，Eloquent 会把它当作显式值写进语句，于是直接报
 * SQLSTATE[23000] 1048 Column 'xxx' cannot be null。
 *
 * 这里在 saving 阶段统一回填，写入口径只有一个：后台表单、Seeder、命令行写入
 * 都能覆盖。使用方用 $nullDefaults 声明「字段 => 空值默认值」，只需列出
 * 表中 NOT NULL 的列，可空列不要放进来，否则会把「未填写」和「空串」搅在一起。
 *
 * @mixin Model
 */
trait HasNullDefaultsTrait
{
    protected static function bootHasNullDefaultsTrait(): void
    {
        static::saving(function (Model $model): void {
            foreach ($model->nullDefaults as $attribute => $default) {
                if ($model->getAttribute($attribute) !== null) {
                    continue;
                }

                $model->setAttribute($attribute, $default);
            }
        });
    }
}
