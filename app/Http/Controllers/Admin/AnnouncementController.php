<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Http\Requests\Admin\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Support\FileUploads;
use App\Support\PublicationDate;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => 'nullable|string|max:255', 'status' => 'nullable|in:draft,published']);
        $announcements = Announcement::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('title', 'like', '%'.$search.'%')->orWhere('content', 'like', '%'.$search.'%')))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest()->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        return view('admin.announcements.create', ['announcement' => new Announcement]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $this->persist($request, new Announcement(['user_id' => $request->user()->id]));

        return to_route('admin.announcements.index')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $this->persist($request, $announcement);

        return to_route('admin.announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return to_route('admin.announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function persist(StoreAnnouncementRequest $request, Announcement $announcement): void
    {
        $data = $request->safe()->except('attachment');
        $data['published_at'] = PublicationDate::resolve($data, $announcement->published_at);
        if (! $announcement->exists || $data['title'] !== $announcement->title) {
            $data['slug'] = UniqueSlug::generate(Announcement::withTrashed(), $data['title'], $announcement->id);
        }
        $old = $announcement->attachment_path;
        $new = null;
        try {
            if ($file = $request->file('attachment')) {
                $new = FileUploads::store($file, 'local', 'announcements', 'attachment');
                $data['attachment_path'] = $new;
                $data['attachment_original_name'] = FileUploads::originalName($file->getClientOriginalName());
            }
            DB::transaction(fn () => $announcement->fill($data)->save());
        } catch (Throwable $e) {
            FileUploads::remove('local', [$new]);
            throw $e;
        }
        if ($new) {
            FileUploads::remove('local', [$old]);
        }
    }
}
