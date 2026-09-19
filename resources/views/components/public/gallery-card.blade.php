@props(['album', 'heading' => 2])
@php($level = $heading === 3 ? 3 : 2)
<article class="public-gallery-card">
    <div class="public-card-media">
        @if ($cover = $album->coverUrl())<img class="public-news-card__image" src="{{ $cover }}" alt="{{ $album->title }}" loading="lazy">
        @else<div class="public-news-card__image public-news-card__placeholder" aria-hidden="true">Galeri Desa</div>@endif
        <span class="public-media-label public-photo-count">{{ $album->photos_count }} foto</span>
    </div>
    <div class="public-gallery-card__body">
        @if ($album->event_date)<p class="public-metadata mb-2"><x-admin.date-text :date="$album->event_date" /></p>@endif
        <h{{ $level }} class="h5 public-card-title"><a href="{{ route('gallery.show', $album->slug) }}">{{ $album->title }}</a></h{{ $level }}>
        @if ($album->description)<p class="text-muted mb-3">{{ Str::limit($album->description, 130) }}</p>@endif
        <a class="stretched-link" href="{{ route('gallery.show', $album->slug) }}"><span class="visually-hidden">Lihat galeri: {{ $album->title }}</span></a>
    </div>
</article>
