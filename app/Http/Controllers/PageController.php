<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * 单页详情。
     *
     * 本动作挂在路由兜底（Route::fallback）上：只有 /admin、/install、
     * /livewire-* 等既有路由全都没命中时才会执行。因此根级地址（如 /about）
     * 可以安全地交给单页，既不会抢走后台入口，也不受路由注册顺序影响。
     */
    public function show(Request $request): View
    {
        $page = Page::query()
            ->published()
            ->where('slug', $request->path())
            ->firstOrFail();

        return view('page', compact('page'));
    }
}
