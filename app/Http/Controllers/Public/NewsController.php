<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $newsItems = News::published()
            ->with('category:id,name')
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('public.news.index', compact('newsItems'));
    }

    public function show(string $slug): View
    {
        $news = News::published()
            ->with('category:id,name')
            ->where('slug', $slug)
            ->firstOrFail();

        $news->increment('views');
        $news->refresh()->load('category:id,name');

        $relatedNews = News::published()
            ->with('category:id,name')
            ->where('news_category_id', $news->news_category_id)
            ->whereKeyNot($news->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('public.news.show', compact('news', 'relatedNews'));
    }
}
