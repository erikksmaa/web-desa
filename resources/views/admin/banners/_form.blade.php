<x-admin.validation-summary />
<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
@csrf
@if ($method !== 'POST') @method($method) @endif
<x-admin.form.input name="title" label="Judul" :value="$banner->title" maxlength="255" />
<x-admin.form.textarea name="subtitle" label="Subtitle" :value="$banner->subtitle" maxlength="255" rows="3" />
@if ($banner->image_path && Storage::disk('public')->exists($banner->image_path))
<img src="{{ Storage::disk('public')->url($banner->image_path) }}" class="admin-current-image mb-3" alt="Banner saat ini">
@endif
<x-admin.form.file name="image" label="Gambar banner" accept=".jpg,.jpeg,.png,.webp" :preview="true" :required="!$banner->exists" help="JPEG, PNG, atau WebP maksimal 5 MB. Gunakan gambar lanskap lebar." />
<div class="row"><div class="col-md-6"><x-admin.form.input name="cta_label" label="Label Tombol" :value="$banner->cta_label" maxlength="100" /></div><div class="col-md-6"><x-admin.form.input name="cta_url" label="URL Tombol" :value="$banner->cta_url" maxlength="2048" help="URL lengkap dengan http:// atau https://. Wajib jika label tombol diisi." /></div></div>
<x-admin.form.checkbox name="is_active" label="Aktif" :checked="$banner->is_active ?? true" />
<x-admin.form.input name="sort_order" label="Urutan" type="number" :value="$banner->sort_order ?? 0" min="0" max="4294967295" required />
<div class="row"><div class="col-md-6"><x-admin.form.input name="starts_at" label="Mulai Tayang (WIB)" type="datetime-local" :value="$banner->starts_at?->format('Y-m-d\TH:i')" /></div><div class="col-md-6"><x-admin.form.input name="ends_at" label="Selesai Tayang (WIB)" type="datetime-local" :value="$banner->ends_at?->format('Y-m-d\TH:i')" /></div></div>
<button type="submit" class="btn btn-primary">Simpan banner</button>
<a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">Batal</a>
</form>
