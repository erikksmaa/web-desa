<x-admin.validation-summary />
<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
@csrf
@if ($method !== 'POST') @method($method) @endif
<x-admin.form.input name="name" label="Nama" :value="$official->name" maxlength="150" required />
<x-admin.form.input name="position" label="Jabatan" :value="$official->position" maxlength="150" required />
@if ($official->photo_path && Storage::disk('public')->exists($official->photo_path))
<img src="{{ Storage::disk('public')->url($official->photo_path) }}" class="admin-image-preview mb-3" alt="Foto perangkat saat ini">
@endif
<x-admin.form.file name="photo" label="Foto" accept=".jpg,.jpeg,.png,.webp" :preview="true" help="JPEG, PNG, atau WebP, maksimal 5 MB. Kosongkan untuk mempertahankan foto." />
<x-admin.form.textarea name="biography" label="Biografi" :value="$official->biography" rows="6" />
<x-admin.form.checkbox name="is_active" label="Aktif" :checked="$official->is_active ?? true" />
<x-admin.form.checkbox name="is_village_head" label="Kepala Desa" :checked="$official->is_village_head ?? false" help="Menetapkan kepala desa aktif akan menggantikan penanda kepala desa sebelumnya. Penanda dihapus jika perangkat dinonaktifkan." />
<x-admin.form.input name="sort_order" label="Urutan" type="number" :value="$official->sort_order ?? 0" min="0" max="4294967295" required />
<button class="btn btn-primary" type="submit">Simpan perangkat desa</button>
<a class="btn btn-outline-secondary" href="{{ route('admin.village-officials.index') }}">Batal</a>
</form>
