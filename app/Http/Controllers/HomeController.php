<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Document;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\VillageOfficial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $banners = Banner::currentlyVisible()->orderBy('sort_order')->orderBy('id')->limit(10)->get();
        $villageHead = VillageOfficial::active()->where('is_village_head', true)->orderBy('id')->first();
        $newsItems = News::published()->with('category')->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get();
        $announcements = Announcement::published()->orderByDesc('published_at')->orderByDesc('id')->limit(4)->get();
        $agendas = Agenda::published()->whereRaw('COALESCE(end_at, start_at) >= ?', [now()])
            ->orderBy('start_at')->orderBy('id')->limit(4)->get();
        $documents = Document::published()->with('category')->orderByDesc('published_at')->orderByDesc('id')->limit(4)->get();
        $albums = GalleryAlbum::published()->with('firstPhoto')->withCount('photos')
            ->orderByDesc('event_date')->orderByDesc('id')->limit(3)->get();

        return view('public.home.index', compact('banners', 'villageHead', 'newsItems', 'announcements', 'agendas', 'documents', 'albums'));
    }
}
