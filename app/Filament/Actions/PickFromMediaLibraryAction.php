<?php

namespace App\Filament\Actions;

use App\Filament\Resources\Media\Tables\MediaPickerTable;
use App\Models\Media;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TableSelect;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

/**
 * 「从媒体库选择」：在图片字段旁开一个入口，弹窗里挑一张媒体库已有的图片，
 * 把该文件路径回填到字段上（不复制文件，字段只是引用它）。
 */
class PickFromMediaLibraryAction
{
    /**
     * 挂到图片字段上：`->hintAction(PickFromMediaLibraryAction::hint())`
     *
     * 闭包在渲染时才求值，因此能拿到字段最终的状态路径
     * （资源表单是 `data.thumb`，系统设置是 `data.site_logo` 之类）。
     */
    public static function hint(): Closure
    {
        return static fn (FileUpload $component): Action => static::make($component->getStatePath());
    }

    /**
     * @param  string  $targetStatePath  要回填的字段状态路径（绝对路径）
     */
    public static function make(string $targetStatePath): Action
    {
        return Action::make('pickFromMediaLibrary')
            ->label('从媒体库选择')
            ->icon(Heroicon::OutlinedPhoto)
            ->modalHeading('从媒体库选择图片')
            ->modalDescription('点表格里的行选中图片，再点下方按钮。字段引用的是媒体库里的这个文件，若在媒体库中删除它，前台图片也会失效。')
            ->modalSubmitActionLabel('使用这张图片')
            ->modalWidth(Width::FourExtraLarge)
            ->schema([
                TableSelect::make('media_id')
                    ->label('媒体库图片')
                    ->tableConfiguration(MediaPickerTable::class)
                    ->required()
                    ->helperText('表格上方可按文件名搜索，右下角可翻页。'),
            ])
            ->action(function (array $data, Set $set) use ($targetStatePath): void {
                $media = Media::query()->find($data['media_id'] ?? null);

                if (! $media instanceof Media || blank($media->path)) {
                    Notification::make()
                        ->title('这张图片已不存在')
                        ->danger()
                        ->send();

                    return;
                }

                if (($media->disk ?: 'public') !== 'public') {
                    Notification::make()
                        ->title('该文件不在 public 磁盘，无法作为站点图片使用')
                        ->danger()
                        ->send();

                    return;
                }

                // 传数组是为了和 FileUploadStateCast::set() 的结果保持一致：
                // 上传组件的原始状态是「uuid 键 => 路径」的映射，而不是纯字符串。
                $set($targetStatePath, [(string) Str::uuid() => $media->path], isAbsolute: true);

                Notification::make()
                    ->title('已引用媒体库图片：' . $media->name)
                    ->success()
                    ->send();
            });
    }
}
