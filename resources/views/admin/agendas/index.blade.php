@extends('layouts.admin')
@section('title', 'Agenda')
@section('content')
<x-admin.page-header title="Agenda"><x-slot:actions><a class="btn btn-primary" href="{{ route('admin.agendas.create') }}">Tambah agenda</a></x-slot:actions></x-admin.page-header>
<x-admin.card>
<div class="mb-3"><x-admin.search-form :action="route('admin.agendas.index')" placeholder="Cari agenda…" /></div>
<x-admin.filter-form :action="route('admin.agendas.index')" :clear-url="route('admin.agendas.index')">
<div class="col-sm-4"><label class="form-label" for="filter-status">Status</label><select class="form-select" id="filter-status" name="status"><option value="">Semua status</option><option value="draft" @selected(request('status') === 'draft')>Draf</option><option value="published" @selected(request('status') === 'published')>Terbit</option></select></div>

</x-admin.filter-form>
@if ($agendas->isEmpty())
<x-admin.empty-state title="Tidak ada data" message="Belum ada data yang sesuai dengan pencarian." />
@else
<div class="mt-4"><x-admin.table><x-slot:caption>Daftar agenda</x-slot:caption><x-slot:head><tr><th scope="col">Judul</th><th scope="col">Lokasi</th><th scope="col">Mulai (WIB)</th><th scope="col">Selesai (WIB)</th><th scope="col">Status</th><th scope="col" class="text-end">Tindakan</th></tr></x-slot:head>
@foreach ($agendas as $item)
<tr><td>{{ $item->title }}</td><td>{{ $item->location ?: '—' }}</td><td><x-admin.date-text :date="$item->start_at" :time="true" /></td><td><x-admin.date-text :date="$item->end_at" :time="true" /></td><td><x-admin.status-badge :status="$item->status" /></td><td><x-admin.table-actions><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.agendas.edit', $item) }}">Ubah</a><x-admin.confirm-button :action="route('admin.agendas.destroy', $item)" :item="$item->title" message="Hapus data ini dari daftar publik?" /></x-admin.table-actions></td></tr>
@endforeach
</x-admin.table></div>
<div class="mt-4">{{ $agendas->links() }}</div>
@endif
</x-admin.card>
@endsection
