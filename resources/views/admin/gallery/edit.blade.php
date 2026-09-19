@extends('layouts.admin')
@section('title', 'Ubah Album Galeri')
@section('content')
<x-admin.breadcrumb :items="[['label'=>'Dashboard','url'=>route('admin.dashboard')],['label'=>'Galeri','url'=>route('admin.gallery.index')],['label'=>'Ubah album']]" />
<x-admin.page-header title="Ubah Album Galeri" />
<div class="mb-4"><x-admin.card>
@include('admin.gallery._form', ['action'=>route('admin.gallery.update',$galleryAlbum),'method'=>'PUT'])
</x-admin.card></div>
<div class="mb-4"><x-admin.card title="Tambah foto">
<form action="{{ route('admin.gallery.photos.store',$galleryAlbum) }}" method="POST" enctype="multipart/form-data">
@csrf
<x-admin.form.file name="photos[]" error-key="photos" id="additional-photos" label="Pilih foto" accept=".jpg,.jpeg,.png,.webp" multiple required help="Maksimal 10 foto per unggahan, 5 MB per foto. JPG, PNG, atau WebP." />
<button class="btn btn-primary" type="submit">Unggah foto</button>
</form>
</x-admin.card></div>
<h2 class="h4 mb-3">Foto album</h2>
<div class="row g-4">
@forelse ($photos as $photo)
<div class="col-md-6 col-xl-4">
<x-admin.card>
@if (\Illuminate\Support\Facades\Storage::disk('public')->exists($photo->image_path))
<img class="media-landscape rounded mb-3" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->image_path) }}" alt="{{ $photo->alt_text ?: $photo->caption ?: $galleryAlbum->title }}" loading="lazy">
@else
<p class="bg-light p-4">Gambar tidak tersedia.</p>
@endif
<form method="POST" action="{{ route('admin.gallery.photos.update',[$galleryAlbum,$photo]) }}">
@csrf @method('PATCH')
<x-admin.form.input :name="'metadata['.$photo->id.'][caption]'" :error-key="'metadata.'.$photo->id.'.caption'" :id="'caption-'.$photo->id" label="Caption" :value="$photo->caption" maxlength="255" />
<x-admin.form.input :name="'metadata['.$photo->id.'][alt_text]'" :error-key="'metadata.'.$photo->id.'.alt_text'" :id="'alt-'.$photo->id" label="Teks alternatif" :value="$photo->alt_text" maxlength="255" help="Jelaskan isi foto untuk pengguna pembaca layar." />
<x-admin.form.input :name="'metadata['.$photo->id.'][sort_order]'" :error-key="'metadata.'.$photo->id.'.sort_order'" :id="'order-'.$photo->id" label="Urutan" type="number" :value="$photo->sort_order" min="0" max="4294967295" required />
<div class="d-flex flex-wrap gap-2"><button class="btn btn-outline-primary btn-sm" type="submit">Simpan foto</button>
<x-admin.confirm-button :action="route('admin.gallery.photos.destroy',[$galleryAlbum,$photo])" :item="$photo->caption ?: 'Foto album'" message="Foto dan file gambar akan dihapus permanen." /></div>
</form>
</x-admin.card>
</div>
@empty
<div class="col-12"><x-admin.empty-state title="Belum ada foto" message="Unggah foto untuk melengkapi album ini." /></div>
@endforelse
</div>
<div class="mt-4">{{ $photos->links() }}</div>
@endsection
