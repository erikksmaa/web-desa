<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $albums = GalleryAlbum::published()->with('firstPhoto')->withCount('photos')
            ->orderBy('sort_order')->latest('event_date')->orderByDesc('id')->paginate(12)->withQueryString();

        return view('public.gallery.index', compact('albums'));
    }

    public function show(string $slug): View
    {
        $album = GalleryAlbum::published()->where('slug', $slug)->firstOrFail();
        $photos = $album->photos()->orderBy('sort_order')->orderBy('id')->paginate(24)->withQueryString();

        return view('public.gallery.show', compact('album', 'photos'));
    }
}
