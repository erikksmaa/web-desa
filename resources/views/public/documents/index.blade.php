@extends('layouts.public')
@section('title', 'Berkas dan Unduhan — '.config('app.name'))
@section('content')
<x-public.page-header title="Berkas dan Unduhan" eyebrow="Pusat dokumen" description="Dokumen publik dan informasi resmi desa yang dapat diunduh." variant="documents" />
<div class="container public-section">
<form action="{{ route('documents.index') }}" method="GET" class="row g-3 public-filter-panel mb-4">
<div class="col-md-5"><label class="form-label" for="document-search">Cari judul berkas</label><input class="form-control" id="document-search" name="search" type="search" maxlength="255" value="{{ request('search') }}"></div>
<div class="col-md-4"><label class="form-label" for="document-category">Kategori</label><select class="form-select" id="document-category" name="category"><option value="">Semua kategori</option>@foreach ($categories as $id=>$name)<option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $name }}</option>@endforeach</select></div>
<div class="col-md-3 d-flex gap-2 align-items-end"><button class="btn btn-primary" type="submit">Cari</button><a class="btn btn-outline-secondary" href="{{ route('documents.index') }}">Atur ulang</a></div>
</form>
<div class="public-document-list">
@forelse ($documents as $item)
<x-public.document-item :item="$item" />
@empty
<p class="public-empty-state mb-0">Tidak ada berkas yang sesuai.</p>
@endforelse
</div>
<div class="public-pagination">{{ $documents->links() }}</div>
</div>
@endsection
