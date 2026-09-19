@extends('layouts.admin')
@section('title', 'Berkas')
@section('content')
<x-admin.page-header title="Berkas"><x-slot:actions><a class="btn btn-primary" href="{{ route('admin.documents.create') }}">Tambah berkas</a></x-slot:actions></x-admin.page-header>
<x-admin.card>
<div class="mb-3"><x-admin.search-form :action="route('admin.documents.index')" placeholder="Cari berkas…" /></div>
<x-admin.filter-form :action="route('admin.documents.index')" :clear-url="route('admin.documents.index')">
<div class="col-sm-4"><label class="form-label" for="filter-status">Status</label><select class="form-select" id="filter-status" name="status"><option value="">Semua status</option><option value="draft" @selected(request('status') === 'draft')>Draf</option><option value="published" @selected(request('status') === 'published')>Terbit</option></select></div>
<div class="col-sm-4"><label class="form-label" for="filter-category">Kategori</label><select class="form-select" name="category" id="filter-category"><option value="">Semua kategori</option>@foreach ($categories as $id=>$name)<option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $name }}</option>@endforeach</select></div><div class="col-12"><a href="{{ route('admin.document-categories.index') }}">Kelola kategori berkas</a></div>
</x-admin.filter-form>
@if ($documents->isEmpty())
<x-admin.empty-state title="Tidak ada data" message="Belum ada data yang sesuai dengan pencarian." />
@else
<div class="mt-4"><x-admin.table><x-slot:caption>Daftar berkas</x-slot:caption><x-slot:head><tr><th scope="col">Judul / Kategori</th><th scope="col">File</th><th scope="col">Status</th><th scope="col">Publikasi</th><th scope="col">Unduhan</th><th scope="col" class="text-end">Tindakan</th></tr></x-slot:head>
@foreach ($documents as $item)
<tr><td>{{ $item->title }}<div class="small text-muted">{{ $item->category->name }}</div></td><td>{{ strtoupper(pathinfo($item->original_filename,PATHINFO_EXTENSION)) }} · {{ \App\Support\HumanFileSize::format($item->file_size) }}</td><td><x-admin.status-badge :status="$item->status" /></td><td><x-admin.date-text :date="$item->published_at" :time="true" /></td><td>{{ number_format($item->download_count,0,',','.') }}</td><td><x-admin.table-actions><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.documents.edit', $item) }}">Ubah</a><x-admin.confirm-button :action="route('admin.documents.destroy', $item)" :item="$item->title" message="Hapus data ini dari daftar publik?" /></x-admin.table-actions></td></tr>
@endforeach
</x-admin.table></div>
<div class="mt-4">{{ $documents->links() }}</div>
@endif
</x-admin.card>
@endsection
