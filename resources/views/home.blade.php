@extends('layouts.app')

@section('title', '首页')

@section('content')
    @if ($slides->isNotEmpty())
        @php $hero = $slides->first(); @endphp
        <section class="mx-auto max-w-6xl px-4 pt-8">
            <a href="{{ route('news.show', $hero) }}" class="group relative block overflow-hidden rounded-2xl">
                <img src="{{ $hero->thumb_url }}" alt="{{ $hero->title }}"
                     class="h-64 w-full object-cover transition duration-500 group-hover:scale-105 md:h-96">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                <div class="absolute bottom-0 left-0 p-6 text-white md:p-8">
                    @if ($hero->category)
                        <span class="rounded-full bg-blue-600 px-3 py-1 text-xs font-medium">
                            {{ $hero->category->cat_name }}
                        </span>
                    @endif
                    <h1 class="mt-3 text-2xl font-bold md:text-3xl">{{ $hero->title }}</h1>
                    <p class="mt-2 max-w-2xl text-sm text-white/80">
                        {{ \Illuminate\Support\Str::limit(strip_tags($hero->description), 120) }}
                    </p>
                </div>
            </a>
        </section>
    @endif

    <section class="mx-auto max-w-6xl px-4 py-10">
        <h2 class="text-lg font-semibold text-gray-900">最新发布</h2>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($latest as $item)
                @include('partials.news-card', ['item' => $item])
            @empty
                <p class="col-span-full py-16 text-center text-gray-400">暂无内容</p>
            @endforelse
        </div>
    </section>

    @if ($recommended->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pb-16">
            <h2 class="text-lg font-semibold text-gray-900">热门阅读</h2>

            <ol class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                @foreach ($recommended as $index => $item)
                    <li class="flex items-center gap-4 px-5 py-3">
                        <span class="w-5 text-center text-sm font-bold {{ $index < 3 ? 'text-blue-600' : 'text-gray-300' }}">
                            {{ $index + 1 }}
                        </span>
                        <a href="{{ route('news.show', $item) }}"
                           class="flex-1 truncate text-sm text-gray-700 transition hover:text-blue-600">
                            {{ $item->title }}
                        </a>
                        <span class="shrink-0 text-xs text-gray-400">{{ $item->views }} 次阅读</span>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif
@endsection
