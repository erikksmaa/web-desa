<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\FileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::published()->latest('published_at')->orderByDesc('id')->paginate(12)->withQueryString();

        return view('public.announcements.index', compact('announcements'));
    }

    public function show(string $slug): View
    {
        $announcement = Announcement::published()->where('slug', $slug)->firstOrFail();

        return view('public.announcements.show', compact('announcement'));
    }

    public function download(string $slug): StreamedResponse
    {
        $announcement = Announcement::published()->where('slug', $slug)->firstOrFail();
        abort_unless($announcement->attachment_path && Storage::disk('local')->exists($announcement->attachment_path), 404);

        return Storage::disk('local')->download($announcement->attachment_path, FileUploads::originalName($announcement->attachment_original_name ?: 'lampiran'), ['X-Content-Type-Options' => 'nosniff']);
    }
}
