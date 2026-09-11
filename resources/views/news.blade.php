@extends('layouts.app')

@section('title', $news->title)
@section('keywords', $news->keywords ?: setting('site_keywords'))
@section('description', \Illuminate\Support\Str::limit(strip_tags($news->description), 150))

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-8">
        <nav class="text-xs text-gray-400">
            <a href="{{ route('home') }}" class="transition hover:text-blue-600">首页</a>
            @if ($news->category)
                <span class="mx-1">/</span>
                <a href="{{ route('category.show', $news->category) }}" class="transition hover:text-blue-600">
                    {{ $news->category->cat_name }}
                </a>
            @endif
            <span class="mx-1">/</span>
            <span class="text-gray-600">正文</span>
        </nav>

        <h1 class="mt-3 text-2xl font-bold leading-snug text-gray-900 md:text-3xl">{{ $news->title }}</h1>

        <div class="mt-4 flex flex-wrap items-center gap-4 border-b border-gray-100 pb-4 text-sm text-gray-400">
            <span>{{ $news->input_time?->format('Y-m-d H:i') }}</span>
            <span>{{ $news->views }} 次阅读</span>
            @if ($news->copyfrom)
                <span>来源：{{ $news->copyfrom }}</span>
            @endif
        </div>

        @if ($news->thumb_url)
            <img src="{{ $news->thumb_url }}" alt="{{ $news->title }}" class="mt-6 w-full rounded-xl object-cover">
        @endif

        <div class="mt-6 leading-relaxed text-gray-700 [&_a]:text-blue-600 [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-gray-200 [&_blockquote]:pl-4 [&_blockquote]:text-gray-500 [&_h2]:mt-6 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-gray-900 [&_h3]:mt-4 [&_h3]:text-lg [&_h3]:font-semibold [&_img]:my-4 [&_img]:max-w-full [&_img]:rounded-lg [&_li]:my-1 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:my-4 [&_ul]:list-disc [&_ul]:pl-6">
            {!! $news->content !!}
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-3xl px-4 pb-14">
            <h2 class="text-lg font-semibold text-gray-900">相关阅读</h2>

            <ul class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                @foreach ($related as $item)
                    <li class="px-5 py-3">
                        <a href="{{ route('news.show', $item) }}"
                           class="flex items-center justify-between gap-4 text-sm text-gray-700 transition hover:text-blue-600">
                            <span class="truncate">{{ $item->title }}</span>
                            <span class="shrink-0 text-xs text-gray-400">{{ $item->input_time?->format('Y-m-d') }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
