{{-- 媒体库「地址」列点击后的预览弹窗：图片放大显示，其它文件给出打开原文件的入口 --}}
@php
    /** @var \App\Models\Media $media */
    $url = $media->url;
@endphp

<div style="display: flex; flex-direction: column; align-items: center; gap: .75rem;">
    @if ($media->isImage() && filled($url))
        <img src="{{ $url }}" alt="{{ $media->name }}"
             style="max-width: 100%; max-height: 70vh; border-radius: 8px;">
    @else
        <div style="padding: 1rem 0; color: #6b7280;">
            {!! \Filament\Support\generate_icon_html(
                \Filament\Support\Icons\Heroicon::OutlinedDocument,
                size: \Filament\Support\Enums\IconSize::ExtraLarge,
            )?->toHtml() !!}
        </div>
        <p style="margin: 0; font-size: .875rem; color: #6b7280;">
            这个文件不是图片，无法直接预览
        </p>
    @endif

    <p style="margin: 0; font-size: .875rem; text-align: center; word-break: break-all;">
        {{ $media->name }} · {{ $media->human_size }}
    </p>

    @if (filled($url))
        <a href="{{ $url }}" target="_blank" rel="noopener"
           style="font-size: .875rem; color: #2563eb; text-decoration: underline;">
            {{ $media->isImage() ? '在新标签页打开原图' : '打开文件' }}
        </a>
    @endif
</div>
