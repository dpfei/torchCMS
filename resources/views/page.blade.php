@extends('layouts.app')

@section('title', $page->title)
@section('keywords', $page->keywords ?: setting('site_keywords'))
@section('description', \Illuminate\Support\Str::limit(strip_tags($page->description ?: $page->content), 150))

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-8">
        <nav class="text-xs text-gray-400">
            <a href="{{ route('home') }}" class="transition hover:text-blue-600">首页</a>
            <span class="mx-1">/</span>
            <span class="text-gray-600">{{ $page->title }}</span>
        </nav>

        <h1 class="mt-3 text-2xl font-bold leading-snug text-gray-900 md:text-3xl">{{ $page->title }}</h1>

        @if ($page->thumb_url)
            <img src="{{ $page->thumb_url }}" alt="{{ $page->title }}" class="mt-6 w-full rounded-xl object-cover">
        @endif

        <div class="mt-6 leading-relaxed text-gray-700 [&_a]:text-blue-600 [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-gray-200 [&_blockquote]:pl-4 [&_blockquote]:text-gray-500 [&_h2]:mt-6 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-gray-900 [&_h3]:mt-4 [&_h3]:text-lg [&_h3]:font-semibold [&_img]:my-4 [&_img]:max-w-full [&_img]:rounded-lg [&_li]:my-1 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:my-4 [&_ul]:list-disc [&_ul]:pl-6">
            {!! $page->content !!}
        </div>
    </article>
@endsection
