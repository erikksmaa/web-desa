@extends('layouts.admin')
@section('title', 'Pengumuman')
@section('content')
<x-admin.page-header title="Pengumuman"><x-slot:actions><a class="btn btn-primary" href="{{ route('admin.announcements.create') }}">Tambah pengumuman</a></x-slot:actions></x-admin.page-header>
<x-admin.card>
<div class="mb-3"><x-admin.search-form :action="route('admin.announcements.index')" placeholder="Cari pengumuman…" /></div>
<x-admin.filter-form :action="route('admin.announcements.index')" :clear-url="route('admin.announcements.index')">
<div class="col-sm-4"><label class="form-label" for="filter-status">Status</label><select class="form-select" id="filter-status" name="status"><option value="">Semua status</option><option value="draft" @selected(request('status') === 'draft')>Draf</option><option value="published" @selected(request('status') === 'published')>Terbit</option></select></div>

</x-admin.filter-form>
@if ($announcements->isEmpty())
<x-admin.empty-state title="Tidak ada data" message="Belum ada data yang sesuai dengan pencarian." />
@else
<div class="mt-4"><x-admin.table><x-slot:caption>Daftar pengumuman</x-slot:caption><x-slot:head><tr><th scope="col">Judul</th><th scope="col">Status</th><th scope="col">Publikasi</th><th scope="col">Berakhir</th><th scope="col">Lampiran</th><th scope="col" class="text-end">Tindakan</th></tr></x-slot:head>
@foreach ($announcements as $item)
<tr><td>{{ $item->title }}</td><td><x-admin.status-badge :status="$item->status" /> @if ($item->expires_at && $item->expires_at->lte(now()))<x-admin.status-badge status="expired" />@endif</td><td><x-admin.date-text :date="$item->published_at" :time="true" /></td><td><x-admin.date-text :date="$item->expires_at" :time="true" /></td><td>{{ $item->attachment_path ? 'Ada' : '—' }}</td><td><x-admin.table-actions><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.announcements.edit', $item) }}">Ubah</a><x-admin.confirm-button :action="route('admin.announcements.destroy', $item)" :item="$item->title" message="Hapus data ini dari daftar publik?" /></x-admin.table-actions></td></tr>
@endforeach
</x-admin.table></div>
<div class="mt-4">{{ $announcements->links() }}</div>
@endif
</x-admin.card>
@endsection
