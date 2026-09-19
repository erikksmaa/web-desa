<x-admin.validation-summary />
<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
@csrf
@if ($method !== 'POST') @method($method) @endif
<x-admin.form.input name="title" label="Judul" :value="$galleryAlbum->title" maxlength="255" required />
<x-admin.form.textarea name="description" label="Deskripsi" :value="$galleryAlbum->description" rows="6" />
<div class="row"><div class="col-md-6"><x-admin.form.input name="event_date" label="Tanggal Kegiatan" type="date" :value="$galleryAlbum->event_date?->format('Y-m-d')" /></div>
<div class="col-md-6"><x-admin.form.input name="sort_order" label="Urutan" type="number" :value="$galleryAlbum->sort_order ?? 0" min="0" max="4294967295" required /></div></div>
<x-admin.form.select name="status" label="Status" :options="['draft'=>'Draf','published'=>'Terbit']" :value="$galleryAlbum->status ?? 'draft'" required />
@if ($galleryAlbum->cover_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($galleryAlbum->cover_image))
<img class="admin-current-image mb-3" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($galleryAlbum->cover_image) }}" alt="Sampul album saat ini">
@endif
<x-admin.form.file name="cover" label="Sampul album" :preview="true" accept=".jpg,.jpeg,.png,.webp" help="Opsional. JPG, PNG, WebP maksimal 5 MB. Tanpa sampul, foto pertama digunakan." />
@if (! $galleryAlbum->exists)
<x-admin.form.file name="photos[]" error-key="photos" label="Foto album" accept=".jpg,.jpeg,.png,.webp" multiple help="Opsional, maksimal 10 foto; 5 MB per foto." />
@endif
<button class="btn btn-primary" type="submit">Simpan album</button>
<a class="btn btn-outline-secondary" href="{{ route('admin.gallery.index') }}">Kembali ke galeri</a>
</form>
