<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'name',
        'path',
        'disk',
        'mime_type',
        'size',
        'extension',
        'admin_id',
    ];

    protected $casts = [
        'size' => 'integer',
        'admin_id' => 'integer',
    ];

    protected $appends = [
        'url',
    ];

    /**
     * 文件访问地址
     */
    protected function url(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (blank($this->path)) {
                return null;
            }

            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk($this->disk ?: 'public');

            return $disk->url($this->path);
        });
    }

    /**
     * 上传人
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    protected static function booted(): void
    {
        static::creating(function (Media $media) {
            $media->disk = $media->disk ?: 'public';

            if (blank($media->admin_id)) {
                $media->admin_id = Filament::auth()->id();
            }

            if (blank($media->path)) {
                return;
            }

            /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
            $storage = Storage::disk($media->disk);

            if (blank($media->name)) {
                $media->name = basename($media->path);
            }

            if ($storage->exists($media->path)) {
                $media->size = $media->size ?: $storage->size($media->path);
                $media->mime_type = $media->mime_type ?: $storage->mimeType($media->path);
            }

            $media->extension = $media->extension ?: pathinfo($media->path, PATHINFO_EXTENSION);
        });

        // 删除记录时同步删除物理文件
        static::deleting(function (Media $media) {
            if (filled($media->path)) {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk = Storage::disk($media->disk ?: 'public');
                $disk->delete($media->path);
            }
        });
    }

    /**
     * 人类可读的文件大小
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = (int) $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $index = 0;
        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }
}
