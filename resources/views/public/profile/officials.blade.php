@extends('layouts.public')
@section('title', 'Perangkat Desa — '.$siteSettings->get('site.name', config('app.name')))
@section('description', 'Daftar perangkat dan kepala desa.')
@section('content')
<x-public.page-header title="Perangkat Desa" eyebrow="Pemerintah desa" :description="'Mengenal perangkat yang melayani '.$siteSettings->get('site.name', config('app.name')).'.'" variant="profile" />
<div class="container public-section"><div class="public-official-grid">
@forelse ($officials as $official)
<article class="public-official-card">
@if ($official->photo_path && Storage::disk('public')->exists($official->photo_path))
<img class="public-official-photo" src="{{ Storage::disk('public')->url($official->photo_path) }}" alt="{{ $official->name }}" loading="lazy">
@else<div class="public-official-photo public-news-card__placeholder" role="img" aria-label="Foto belum tersedia">Foto belum tersedia</div>@endif
<div class="public-official-card__body">
@if ($official->is_village_head)<span class="public-category-label mb-2">Kepala Desa</span>@endif
<h2 class="h5">{{ $official->name }}</h2><p class="text-muted">{{ $official->position }}</p>
@if ($official->biography)<p class="public-plain-text small mb-0">{{ Str::limit($official->biography, 320) }}</p>@endif
</div></article>
@empty
<div class="col-12"><p class="public-empty-state">Informasi perangkat desa belum tersedia.</p></div>
@endforelse
</div><div class="public-pagination">{{ $officials->links() }}</div></div>
@endsection
