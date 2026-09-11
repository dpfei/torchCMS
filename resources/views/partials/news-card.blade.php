<article class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
    <a href="{{ route('news.show', $item) }}" class="block">
        @if ($item->thumb_url)
            <img src="{{ $item->thumb_url }}" alt="{{ $item->title }}" class="h-44 w-full object-cover">
        @else
            <div class="flex h-44 w-full items-center justify-center bg-gray-100 text-sm text-gray-300">暂无图片</div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-center gap-2 text-xs text-gray-400">
            @if ($item->category)
                <span class="rounded bg-blue-50 px-2 py-0.5 text-blue-600">{{ $item->category->cat_name }}</span>
            @endif
            <time>{{ $item->input_time?->format('Y-m-d') }}</time>
        </div>

        <h3 class="mt-2 line-clamp-2 font-semibold text-gray-900 transition group-hover:text-blue-600">
            <a href="{{ route('news.show', $item) }}">{{ $item->title }}</a>
        </h3>

        <p class="mt-2 line-clamp-2 flex-1 text-sm text-gray-500">
            {{ \Illuminate\Support\Str::limit(strip_tags($item->description), 80) }}
        </p>

        <div class="mt-4 text-xs text-gray-400">{{ $item->views }} 次阅读</div>
    </div>
</article>
