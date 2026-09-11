<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use App\Support\ContentCache;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function show(News $news): View
    {
        abort_unless($news->status === News::STATUS_ENABLED, 404);

        // 浏览量统计：走 increment，不会触发 saved 事件，因此不会让列表缓存失效
        $news->increment('views');

        $related = ContentCache::remember(
            'news.' . $news->getKey() . '.related',
            fn () => News::query()
                ->published()
                ->where('cat_id', $news->cat_id)
                ->whereKeyNot($news->getKey())
                ->orderByDesc('id')
                ->limit(6)
                ->get()
        );

        $categories = ContentCache::remember(
            'categories.menus',
            fn () => Category::query()->menus()->get()
        );

        return view('news', compact('news', 'related', 'categories'));
    }
}
