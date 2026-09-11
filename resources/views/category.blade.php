@extends('layouts.app')

@section('title', $category->cat_name)
@section('description', $category->description ?: setting('site_description'))

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">
        <nav class="text-xs text-gray-400">
            <a href="{{ route('home') }}" class="transition hover:text-blue-600">首页</a>
            <span class="mx-1">/</span>
            <span class="text-gray-600">{{ $category->cat_name }}</span>
        </nav>

        <h1 class="mt-3 text-2xl font-bold text-gray-900">{{ $category->cat_name }}</h1>

        @if ($category->description)
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-gray-500">{{ $category->description }}</p>
        @endif

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($news as $item)
                @include('partials.news-card', ['item' => $item])
            @empty
                <p class="col-span-full py-16 text-center text-gray-400">该栏目暂无内容</p>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $news->links() }}
        </div>
    </div>
@endsection
