@extends('layouts.public')
@section('title', $album->title.' — '.config('app.name'))
@section('description', \Illuminate\Support\Str::limit($album->description,155))
@section('content')
<div class="container public-section public-gallery-album">
<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item"><a href="{{ route('gallery.index') }}">Galeri</a></li><li class="breadcrumb-item active" aria-current="page">Album</li></ol></nav>
<p class="public-eyebrow">Album kegiatan</p>
<h1>{{ $album->title }}</h1>
@if ($album->event_date)<p class="text-muted">{{ \App\Support\IndonesianDate::format($album->event_date) }}</p>@endif
<p class="mb-5 public-plain-text public-content-width public-gallery-album__intro">{{ $album->description }}</p>
<div class="public-photo-grid">
@forelse ($photos as $photo)
<figure class="public-photo-card">
@if (\Illuminate\Support\Facades\Storage::disk('public')->exists($photo->image_path))
<a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->image_path) }}" aria-label="Lihat gambar penuh: {{ $photo->alt_text ?: $photo->caption ?: $album->title }}">
<img class="card-img-top media-landscape" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->image_path) }}" alt="{{ $photo->alt_text ?: $photo->caption ?: $album->title }}" loading="lazy"></a>
@else<div class="media-landscape public-news-card__placeholder">Gambar tidak tersedia</div>@endif
@if ($photo->caption)<figcaption>{{ $photo->caption }}</figcaption>@endif
</figure>
@empty
<div class="col-12"><p class="public-empty-state">Belum ada foto di album ini.</p></div>
@endforelse
</div><div class="mt-4">{{ $photos->links() }}</div>
</div>
@endsection
