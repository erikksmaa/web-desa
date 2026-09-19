@extends('layouts.admin')
@section('title','Perangkat Desa')
@section('content')
<x-admin.page-header title="Perangkat Desa"><x-slot:actions><a class="btn btn-primary" href="{{ route('admin.village-officials.create') }}">Tambah perangkat</a></x-slot:actions></x-admin.page-header>
<x-admin.card>
<x-admin.search-form :action="route('admin.village-officials.index')" placeholder="Cari nama atau jabatan…" />
<x-admin.filter-form :action="route('admin.village-officials.index')" :clear-url="route('admin.village-officials.index')">
<div class="col-sm-4"><label for="filter-active" class="form-label">Status</label><select id="filter-active" name="active" class="form-select"><option value="">Semua status</option><option value="1" @selected(request('active') === '1')>Aktif</option><option value="0" @selected(request('active') === '0')>Nonaktif</option></select></div>
</x-admin.filter-form>
@if ($officials->isEmpty())
<x-admin.empty-state title="Belum ada perangkat" message="Tidak ada perangkat desa yang sesuai dengan pencarian." />
@else
<x-admin.table><x-slot:caption>Daftar perangkat desa</x-slot:caption><x-slot:head><tr><th scope="col">Foto</th><th scope="col">Nama</th><th scope="col">Jabatan</th><th scope="col">Status</th><th scope="col">Urutan</th><th scope="col">Tindakan</th></tr></x-slot:head>
@foreach ($officials as $item)
<tr><td>@if ($item->photo_path && Storage::disk('public')->exists($item->photo_path))<img src="{{ Storage::disk('public')->url($item->photo_path) }}" class="admin-table-portrait" alt="{{ $item->name }}">@else<span class="text-muted">Tanpa foto</span>@endif</td><td>{{ $item->name }} @if ($item->is_village_head)<span class="badge text-bg-light">Kepala Desa</span>@endif</td><td>{{ $item->position }}</td><td><x-admin.status-badge :status="$item->is_active ? 'active' : 'inactive'" /></td><td>{{ $item->sort_order }}</td><td><x-admin.table-actions><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.village-officials.edit',$item) }}">Ubah</a><x-admin.confirm-button :action="route('admin.village-officials.destroy',$item)" :item="$item->name" :message="$item->is_village_head && $item->is_active ? 'Hapus permanen? Identitas kepala desa tidak akan tampil sampai pengganti ditetapkan.' : 'Hapus perangkat dan fotonya secara permanen?'" /></x-admin.table-actions></td></tr>
@endforeach
</x-admin.table>
{{ $officials->links() }}
@endif
</x-admin.card>
@endsection
