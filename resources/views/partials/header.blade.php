@php
    $siteName = setting('site_name', 'torchCMS');
    $logo = setting('site_logo');
@endphp
<header class="sticky top-0 z-40 border-b border-gray-100 bg-white/90 backdrop-blur">
    <div class="mx-auto max-w-6xl px-4">
        <div class="flex h-16 items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-gray-900">
                @if ($logo)
                    <img src="{{ Storage::disk('public')->url($logo) }}" alt="{{ $siteName }}" class="h-8">
                @else
                    <span class="text-blue-600">{{ $siteName }}</span>
                @endif
            </a>

            <nav class="hidden items-center gap-6 md:flex">
                <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 transition hover:text-blue-600">
                    首页
                </a>
                @foreach ($categories ?? [] as $category)
                    <a href="{{ route('category.show', $category) }}"
                       class="text-sm font-medium text-gray-600 transition hover:text-blue-600">
                        {{ $category->cat_name }}
                    </a>
                @endforeach
            </nav>

            <a href="/admin"
               class="hidden rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-gray-700 md:inline-block">
                后台管理
            </a>
        </div>

        <nav class="flex gap-4 overflow-x-auto pb-3 md:hidden">
            <a href="{{ route('home') }}" class="whitespace-nowrap text-sm text-gray-600">首页</a>
            @foreach ($categories ?? [] as $category)
                <a href="{{ route('category.show', $category) }}" class="whitespace-nowrap text-sm text-gray-600">
                    {{ $category->cat_name }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
