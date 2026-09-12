<?php

namespace App\Filament\Resources\Media\Tables;

use App\Models\Media;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * 「从媒体库选择」弹窗里用的表格：只列图片，勾选一行即可回填字段。
 *
 * 与 MediaTable 分开维护，是因为选择器只需要「看得清、搜得到、点得中」，
 * 不需要增删改等管理列。
 */
class MediaPickerTable
{
    /**
     * 兜底判断图片的扩展名：部分环境探测不出 mime_type 时仍能选到图片
     *
     * @var array<int, string>
     */
    protected const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif', 'ico'];

    public static function configure(Table $table): Table
    {
        return $table
            ->query(Media::query()->where('disk', 'public')->where(function (Builder $query): void {
                $query->where('mime_type', 'like', 'image/%')
                    ->orWhereIn('extension', self::IMAGE_EXTENSIONS);
            }))
            ->columns([
                ImageColumn::make('url')
                    ->label('预览')
                    ->height(56),

                TextColumn::make('name')
                    ->label('文件名')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (?Media $record): ?string => $record?->name),

                TextColumn::make('human_size')
                    ->label('大小')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('上传时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([12, 24, 48])
            ->defaultPaginationPageOption(12)
            ->emptyStateHeading('媒体库里还没有图片')
            ->emptyStateDescription('先到左侧「媒体库」上传图片，再回来这里选择。')
            ->emptyStateIcon('heroicon-o-photo');
    }
}
