@extends('layouts.admin')
@section('title', 'Galeri')
@section('content')
<x-admin.page-header title="Galeri"><x-slot:actions><a class="btn btn-primary" href="{{ route('admin.gallery.create') }}">Tambah galeri</a></x-slot:actions></x-admin.page-header>
<x-admin.card>
<div class="mb-3"><x-admin.search-form :action="route('admin.gallery.index')" placeholder="Cari galeri…" /></div>
<x-admin.filter-form :action="route('admin.gallery.index')" :clear-url="route('admin.gallery.index')">
<div class="col-sm-4"><label class="form-label" for="filter-status">Status</label><select class="form-select" id="filter-status" name="status"><option value="">Semua status</option><option value="draft" @selected(request('status') === 'draft')>Draf</option><option value="published" @selected(request('status') === 'published')>Terbit</option></select></div>

</x-admin.filter-form>
@if ($albums->isEmpty())
<x-admin.empty-state title="Tidak ada data" message="Belum ada data yang sesuai dengan pencarian." />
@else
<div class="mt-4"><x-admin.table><x-slot:caption>Daftar galeri</x-slot:caption><x-slot:head><tr><th scope="col">Sampul</th><th scope="col">Judul</th><th scope="col">Kegiatan</th><th scope="col">Status</th><th scope="col">Foto</th><th scope="col">Urutan</th><th scope="col" class="text-end">Tindakan</th></tr></x-slot:head>
@foreach ($albums as $item)
<tr><td>@if ($cover = $item->coverUrl())<img class="admin-news-thumbnail" src="{{ $cover }}" alt="">@else<span class="admin-news-thumbnail">Galeri</span>@endif</td><td>{{ $item->title }}</td><td><x-admin.date-text :date="$item->event_date" /></td><td><x-admin.status-badge :status="$item->status" /></td><td>{{ $item->photos_count }}</td><td>{{ $item->sort_order }}</td><td><x-admin.table-actions><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.gallery.edit', $item) }}">Ubah</a><x-admin.confirm-button :action="route('admin.gallery.destroy', $item)" :item="$item->title" message="Album dan seluruh foto akan dihapus permanen." /></x-admin.table-actions></td></tr>
@endforeach
</x-admin.table></div>
<div class="mt-4">{{ $albums->links() }}</div>
@endif
</x-admin.card>
@endsection
