<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use App\Support\ContentCache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = ContentCache::remember(
            'categories.menus',
            fn () => Category::query()->menus()->get()
        );

        $slides = ContentCache::remember(
            'news.slides',
            fn () => News::query()
                ->published()
                ->where('thumb', '!=', '')
                ->orderByDesc('sort')
                ->orderByDesc('id')
                ->limit(5)
                ->get()
        );

        $latest = ContentCache::remember(
            'news.latest',
            fn () => News::query()
                ->published()
                ->with('category')
                ->orderByDesc('sort')
                ->orderByDesc('id')
                ->limit(9)
                ->get()
        );

        $recommended = ContentCache::remember(
            'news.recommended',
            fn () => News::query()
                ->published()
                ->with('category')
                ->orderByDesc('views')
                ->limit(5)
                ->get()
        );

        return view('home', compact('categories', 'slides', 'latest', 'recommended'));
    }
}
