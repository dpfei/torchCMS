<?php

namespace App\Filament\Actions;

use App\Filament\Forms\Components\MediaLibraryFileUpload;
use App\Filament\Resources\Media\Tables\MediaPickerTable;
use App\Models\Media;
use App\Support\MediaLibrary;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TableSelect;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * 「从媒体库选择」：在图片字段旁开一个入口，弹窗里挑一张媒体库已有的图片，
 * 把该文件路径回填到字段上（不复制文件，字段只是引用它）。
 *
 * 媒体库为空也没关系：弹窗里可以直接上传一张新的，上传的文件会入库并直接选中。
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
            ->modalDescription('选中或上传一张图片即可。字段引用的是媒体库里的这个文件，若在媒体库中删除它，前台图片也会失效。')
            ->modalSubmitActionLabel('使用这张图片')
            ->modalWidth(Width::FourExtraLarge)
            ->schema([
                MediaLibraryFileUpload::make('upload')
                    ->label('直接上传新图片到媒体库')
                    ->image()
                    ->disk('public')
                    ->directory('media')
                    ->maxSize(10240)
                    ->helperText('上传后会自动入库并作为这次的选择，不必再点下面的表格。')
                    ->columnSpanFull(),

                TableSelect::make('media_id')
                    ->label('或者从已有图片里挑一张')
                    ->tableConfiguration(MediaPickerTable::class)
                    ->requiredWithout('upload')
                    ->helperText('点表格里的行即选中；表格上方可按文件名搜索，右下角可翻页。'),
            ])
            ->action(function (array $data, Set $set) use ($targetStatePath): void {
                $media = static::resolvePick($data);

                if (! $media instanceof Media || blank($media->path)) {
                    Notification::make()
                        ->title('请选择一张图片，或先上传一张')
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

    /**
     * 弹窗里可能「上传了新的」也可能「选了已有的」，统一解析成媒体库记录
     *
     * @param  array<string, mixed>  $data  弹窗表单数据
     */
    protected static function resolvePick(array $data): ?Media
    {
        $uploaded = $data['upload'] ?? null;

        if (is_array($uploaded)) {
            $uploaded = Arr::first($uploaded);
        }

        if (is_string($uploaded) && filled($uploaded)) {
            // 上传的文件在落盘时已由 MediaLibraryFileUpload 登记进媒体库
            return MediaLibrary::forPath($uploaded) ?? MediaLibrary::index($uploaded, 'public');
        }

        $id = $data['media_id'] ?? null;

        return filled($id) ? Media::query()->find($id) : null;
    }
}
