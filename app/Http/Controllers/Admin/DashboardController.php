<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Document;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\VillageOfficial;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $summaries = [
            $this->publishingSummary('Berita', News::class),
            $this->publishingSummary('Pengumuman', Announcement::class),
            $this->publishingSummary('Agenda', Agenda::class),
            $this->publishingSummary('Berkas', Document::class),
            $this->publishingSummary('Album Galeri', GalleryAlbum::class),
            [
                'label' => 'Perangkat Desa',
                'total' => VillageOfficial::count(),
                'published' => VillageOfficial::active()->count(),
                'draft' => VillageOfficial::where('is_active', false)->count(),
                'publishedLabel' => 'Aktif',
                'draftLabel' => 'Nonaktif',
            ],
        ];

        $upcomingAgendas = Agenda::published()
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->limit(5)
            ->get(['id', 'title', 'start_at', 'location']);

        $latestNews = News::latest()->limit(5)->get(['id', 'title', 'status', 'created_at']);
        $latestAnnouncements = Announcement::latest()->limit(5)->get(['id', 'title', 'status', 'created_at']);

        return view('admin.dashboard.index', compact(
            'summaries',
            'upcomingAgendas',
            'latestNews',
            'latestAnnouncements',
        ));
    }

    /**
     * @param  class-string<News|Announcement|Agenda|Document|GalleryAlbum>  $model
     * @return array{label: string, total: int, published: int, draft: int}
     */
    private function publishingSummary(string $label, string $model): array
    {
        return [
            'label' => $label,
            'total' => $model::count(),
            'published' => $model::where('status', $model::STATUS_PUBLISHED)->count(),
            'draft' => $model::where('status', $model::STATUS_DRAFT)->count(),
        ];
    }
}
