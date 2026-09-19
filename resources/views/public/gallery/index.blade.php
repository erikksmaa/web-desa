@extends('layouts.public')
@section('title', 'Galeri — '.config('app.name'))
@section('content')
<x-public.page-header title="Galeri Desa" eyebrow="Cerita dalam gambar" description="Dokumentasi kegiatan, pelayanan, pembangunan, dan kebersamaan warga desa." variant="gallery" />
<div class="container public-section"><div class="public-gallery-grid public-gallery-grid--index">
@forelse ($albums as $album)<x-public.gallery-card :album="$album" />
@empty<p class="public-empty-state">Belum ada album galeri.</p>@endforelse
</div><div class="public-pagination">{{ $albums->links() }}</div></div>
@endsection
