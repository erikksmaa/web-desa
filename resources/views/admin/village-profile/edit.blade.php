@extends('layouts.admin')
@section('title', 'Informasi Desa')
@section('content')
<x-admin.page-header title="Informasi Desa" subtitle="Kelola identitas, profil, dan kontak yang ditampilkan kepada masyarakat." />
<x-admin.validation-summary />
<form method="POST" action="{{ route('admin.village-profile.update') }}" enctype="multipart/form-data">
@csrf @method('PATCH')
<div class="mb-4"><x-admin.card title="Identitas Website">
<x-admin.form.input name="site_name" label="Nama Website / Desa" :value="$settings->get('site.name')" required   />
<x-admin.form.input name="site_tagline" label="Tagline" :value="$settings->get('site.tagline')"    />
@if ($image = $settings->image('site.logo'))<img src="{{ $image }}" class="admin-current-image mb-3" alt="Logo saat ini">@endif
<x-admin.form.file name="logo" label="Logo" accept=".jpg,.jpeg,.png,.webp" :preview="true" help="JPEG, PNG, atau WebP, maksimal 5 MB. Kosongkan untuk mempertahankan gambar." />
</x-admin.card></div>
<div class="mb-4"><x-admin.card title="Profil Desa">
<x-admin.form.textarea name="vision" label="Visi" :value="$settings->get('village.vision')"  rows="6"  />
<x-admin.form.textarea name="mission" label="Misi" :value="$settings->get('village.mission')"  rows="6" help="Teks biasa; baris baru dipertahankan tanpa mengubahnya menjadi daftar." />
<x-admin.form.textarea name="history" label="Sejarah Desa" :value="$settings->get('village.history')"  rows="6"  />
<x-admin.form.textarea name="head_welcome" label="Sambutan Kepala Desa" :value="$settings->get('village.head_welcome')"  rows="6"  />
</x-admin.card></div>
<div class="mb-4"><x-admin.card title="Kontak">
<x-admin.form.textarea name="address" label="Alamat" :value="$settings->get('village.address')"  rows="6"  />
<x-admin.form.input name="phone" label="Telepon" :value="$settings->get('village.phone')"    />
<x-admin.form.input name="email" label="Email" :value="$settings->get('village.email')"    />
<x-admin.form.input name="map_url" label="URL Embed Peta" :value="$settings->get('map.embed_url')"   help="URL embed HTTPS Google Maps atau OpenStreetMap; bukan kode iframe." />
</x-admin.card></div>
<div class="mb-4"><x-admin.card title="Media Sosial">
<x-admin.form.input name="facebook" label="Facebook URL" :value="$settings->get('social.facebook')"    />
<x-admin.form.input name="instagram" label="Instagram URL" :value="$settings->get('social.instagram')"    />
<x-admin.form.input name="youtube" label="YouTube URL" :value="$settings->get('social.youtube')"    />
</x-admin.card></div>
<div class="mb-4"><x-admin.card title="Struktur Organisasi">
@if ($image = $settings->image('sotk.image'))<img src="{{ $image }}" class="admin-current-image mb-3" alt="Gambar SOTK saat ini">@endif
<x-admin.form.file name="sotk" label="Gambar SOTK" accept=".jpg,.jpeg,.png,.webp" :preview="true" help="JPEG, PNG, atau WebP, maksimal 5 MB. Kosongkan untuk mempertahankan gambar." />
</x-admin.card></div>
<div class="mb-4"><x-admin.card title="Footer">
<x-admin.form.textarea name="footer_description" label="Deskripsi Footer" :value="$settings->get('footer.description')"  rows="6"  />
</x-admin.card></div>
<button type="submit" class="btn btn-primary">Simpan informasi desa</button>
</form>
@endsection