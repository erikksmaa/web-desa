@extends('layouts.admin')
@section('title','Banner')
@section('content')
<x-admin.page-header title="Banner"><x-slot:actions><a href="{{ route('admin.banners.create') }}" class="btn btn-primary">Tambah banner</a></x-slot:actions></x-admin.page-header>
<x-admin.card>
<x-admin.search-form :action="route('admin.banners.index')" placeholder="Cari judul atau subtitle…" />
<x-admin.filter-form :action="route('admin.banners.index')" :clear-url="route('admin.banners.index')"><div class="col-sm-4"><label for="filter-active" class="form-label">Status</label><select name="active" id="filter-active" class="form-select"><option value="">Semua status</option><option value="1" @selected(request('active') === '1')>Aktif</option><option value="0" @selected(request('active') === '0')>Nonaktif</option></select></div></x-admin.filter-form>
@if ($banners->isEmpty())
<x-admin.empty-state title="Belum ada banner" message="Tidak ada banner yang sesuai dengan pencarian." />
@else
<x-admin.table><x-slot:caption>Daftar banner</x-slot:caption><x-slot:head><tr><th scope="col">Gambar</th><th scope="col">Judul</th><th scope="col">Status</th><th scope="col">Jadwal (WIB)</th><th scope="col">Urutan</th><th scope="col">Tindakan</th></tr></x-slot:head>
@foreach ($banners as $item)
<tr><td>@if ($item->image_path && Storage::disk('public')->exists($item->image_path))<img src="{{ Storage::disk('public')->url($item->image_path) }}" class="admin-news-thumbnail" alt="{{ $item->title ?: 'Banner desa' }}">@else<span class="text-muted">Gambar belum tersedia</span>@endif</td><td>{{ $item->title ?: 'Tanpa judul' }}</td><td><x-admin.status-badge :status="$item->is_active ? 'active' : 'inactive'" /></td><td><div>Mulai: <x-admin.date-text :date="$item->starts_at" time /></div><div>Selesai: <x-admin.date-text :date="$item->ends_at" time /></div></td><td>{{ $item->sort_order }}</td><td><x-admin.table-actions><a href="{{ route('admin.banners.edit',$item) }}" class="btn btn-sm btn-outline-primary">Ubah</a><x-admin.confirm-button :action="route('admin.banners.destroy',$item)" :item="$item->title ?: 'Banner tanpa judul'" message="Hapus banner dan gambarnya secara permanen?" /></x-admin.table-actions></td></tr>
@endforeach
</x-admin.table>
{{ $banners->links() }}
@endif
</x-admin.card>
@endsection
