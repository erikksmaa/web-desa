<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGalleryPhotosRequest;
use App\Http\Requests\Admin\UpdateGalleryPhotoRequest;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Support\FileUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class GalleryPhotoController extends Controller
{
    public function store(StoreGalleryPhotosRequest $request, GalleryAlbum $galleryAlbum): RedirectResponse
    {
        $paths = [];
        try {
            DB::transaction(function () use ($request, $galleryAlbum, &$paths): void {
                $order = (int) $galleryAlbum->photos()->max('sort_order');
                foreach ($request->file('photos') as $file) {
                    $path = FileUploads::store($file, 'public', 'gallery/'.$galleryAlbum->id, 'photos');
                    $paths[] = $path;
                    $galleryAlbum->photos()->create(['image_path' => $path, 'sort_order' => ++$order]);
                }
            });
        } catch (Throwable $e) {
            FileUploads::remove('public', $paths);
            throw $e;
        }

        return to_route('admin.gallery.edit', $galleryAlbum)->with('success', 'Foto berhasil ditambahkan.');
    }

    public function update(UpdateGalleryPhotoRequest $request, GalleryAlbum $galleryAlbum, GalleryPhoto $galleryPhoto): RedirectResponse
    {
        abort_unless($galleryPhoto->gallery_album_id === $galleryAlbum->id, 404);
        $galleryPhoto->update($request->validated('metadata.'.$galleryPhoto->id));

        return to_route('admin.gallery.edit', $galleryAlbum)->with('success', 'Informasi foto berhasil diperbarui.');
    }

    public function destroy(GalleryAlbum $galleryAlbum, GalleryPhoto $galleryPhoto): RedirectResponse
    {
        abort_unless($galleryPhoto->gallery_album_id === $galleryAlbum->id, 404);
        $path = $galleryPhoto->image_path;
        DB::transaction(function () use ($galleryAlbum, $galleryPhoto, $path): void {
            if ($galleryAlbum->cover_image === $path) {
                $galleryAlbum->update(['cover_image' => null]);
            }
            $galleryPhoto->delete();
        });
        FileUploads::remove('public', [$path]);

        return to_route('admin.gallery.edit', $galleryAlbum)->with('success','Foto berhasil dihapus permanen.');
    }
}
