<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGalleryAlbumRequest;
use App\Http\Requests\Admin\UpdateGalleryAlbumRequest;
use App\Models\GalleryAlbum;
use App\Support\FileUploads;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => 'nullable|string|max:255', 'status' => 'nullable|in:draft,published']);
        $albums = GalleryAlbum::with('firstPhoto')->withCount('photos')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('title', 'like', '%'.$search.'%'))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('sort_order')->latest('event_date')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.gallery.index', compact('albums'));
    }

    public function create(): View
    {
        return view('admin.gallery.create', ['galleryAlbum' => new GalleryAlbum]);
    }

    public function store(StoreGalleryAlbumRequest $request): RedirectResponse
    {
        $album = new GalleryAlbum(['user_id' => $request->user()->id]);
        $this->persist($request, $album);

        return to_route('admin.gallery.edit', $album)->with('success', 'Album galeri berhasil ditambahkan.');
    }

    public function edit(GalleryAlbum $galleryAlbum): View
    {
        $photos = $galleryAlbum->photos()->orderBy('sort_order')->orderBy('id')->paginate(24)->withQueryString();

        return view('admin.gallery.edit', compact('galleryAlbum', 'photos'));
    }

    public function update(UpdateGalleryAlbumRequest $request, GalleryAlbum $galleryAlbum): RedirectResponse
    {
        $this->persist($request, $galleryAlbum);

        return to_route('admin.gallery.edit', $galleryAlbum)->with('success', 'Album galeri berhasil diperbarui.');
    }

    public function destroy(GalleryAlbum $galleryAlbum): RedirectResponse
    {
        $paths = DB::transaction(function () use ($galleryAlbum): array {
            $paths = [$galleryAlbum->cover_image, ...$galleryAlbum->photos()->pluck('image_path')->all()];
            $galleryAlbum->delete();

            return $paths;
        });
        FileUploads::remove('public', $paths);

        return to_route('admin.gallery.index')->with('success', 'Album galeri berhasil dihapus permanen.');
    }

    private function persist(StoreGalleryAlbumRequest $request, GalleryAlbum $album): void
    {
        $data = $request->safe()->except(['cover', 'photos']);
        if (! $album->exists || $album->title !== $data['title']) {
            $data['slug'] = UniqueSlug::generate(GalleryAlbum::query(), $data['title'], $album->id);
        }
        $paths = [];
        $oldCover = $album->cover_image;
        try {
            DB::transaction(function () use ($request, $album, $data, &$paths): void {
                $album->fill($data)->save();
                $directory = 'gallery/'.$album->id;
                if ($cover = $request->file('cover')) {
                    $path = FileUploads::store($cover, 'public', $directory, 'cover');
                    $paths[] = $path;
                    $album->update(['cover_image' => $path]);
                }
                $order = (int) $album->photos()->max('sort_order');
                foreach ($request->file('photos', []) as $file) {
                    $path = FileUploads::store($file, 'public', $directory, 'photos');
                    $paths[] = $path;
                    $album->photos()->create(['image_path' => $path, 'sort_order' => ++$order]);
                }
            });
        } catch (Throwable $e) {
            FileUploads::remove('public', $paths);
            throw $e;
        }
        if ($request->hasFile('cover') && $oldCover && ! $album->photos()->where('image_path', $oldCover)->exists()) {
            FileUploads::remove('public',[$oldCover]);
        }
    }
}
