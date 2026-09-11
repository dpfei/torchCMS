<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use App\Support\ContentCache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * 每页条数
     */
    protected const PER_PAGE = 12;

    public function show(Category $category): View
    {
        $page = Paginator::resolveCurrentPage();

        // 只缓存数据本身，分页链接按当前请求实时构造
        $data = ContentCache::remember(
            'category.' . $category->getKey() . '.page.' . $page,
            function () use ($category, $page): array {
                $query = News::query()
                    ->published()
                    ->where('cat_id', $category->getKey());

                return [
                    'items' => (clone $query)
                        ->with('category')
                        ->orderByDesc('sort')
                        ->orderByDesc('id')
                        ->forPage($page, self::PER_PAGE)
                        ->get(),
                    'total' => $query->count(),
                ];
            }
        );

        $news = new LengthAwarePaginator(
            $data['items'],
            $data['total'],
            self::PER_PAGE,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );

        $categories = ContentCache::remember(
            'categories.menus',
            fn () => Category::query()->menus()->get()
        );

        return view('category', compact('category', 'news', 'categories'));
    }
}
