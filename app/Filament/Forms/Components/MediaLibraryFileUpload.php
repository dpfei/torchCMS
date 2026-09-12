<?php

namespace App\Filament\Forms\Components;

use App\Filament\Actions\PickFromMediaLibraryAction;
use App\Support\MediaLibrary;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Arr;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * 站点图片字段的专用上传控件：把裸 FileUpload 需要手动配的三件事一次配好。
 *
 * 1. 上传即入库：新上传的文件落盘后自动登记到媒体库（media 表），
 *    字段上原有的文件（历史上传、从媒体库引用过来的）也会顺手补登记，
 *    媒体库因此就是全站图片总览；
 * 2. 从媒体库选择：label 旁自带「从媒体库选择」入口，可以直接引用库里已有的图；
 * 3. 路径防篡改：只放行媒体库 / 各图片目录里真实存在的路径。
 *
 * 用法与 FileUpload 完全一致，以后新增图片字段照抄这一行即可：
 *
 *     MediaLibraryFileUpload::make('thumb')->image()->disk('public')->directory('news')
 */
class MediaLibraryFileUpload extends FileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->hintAction(PickFromMediaLibraryAction::hint());

        $this->preventFilePathTampering(
            allowFilePathUsing: MediaLibrary::allowExistingFilePath(),
        );

        // 落盘仍然沿用 Filament 默认逻辑，只是拿到路径后顺手登记到媒体库
        // （这里能拿到上传时的原始文件名，媒体库里显示的名字更好看）
        $this->saveUploadedFileUsing(function (TemporaryUploadedFile $file): ?string {
            $path = $this->saveUploadedFile($file);

            if (filled($path)) {
                MediaLibrary::index($path, $this->getDiskName(), $file->getClientOriginalName());
            }

            return $path;
        });
    }

    /**
     * 文件落盘后，把字段里最终留下的路径全部补登记一次
     *
     * 这样「历史上传的文件」和「从媒体库引用过来的文件」也会出现在素材总览里，
     * 而不会只登记本次新传的那一张。同路径只登记一条，重复保存不会产生重复记录。
     */
    public function saveUploadedFiles(): void
    {
        parent::saveUploadedFiles();

        foreach (Arr::wrap($this->getState()) as $path) {
            if (is_string($path) && filled($path)) {
                MediaLibrary::index($path, $this->getDiskName());
            }
        }
    }
}
