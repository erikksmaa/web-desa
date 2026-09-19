@extends('layouts.public')
@section('title', 'Berita — '.config('app.name'))
@section('content')
@php
    $featuredNews = $newsItems->onFirstPage() ? $newsItems->first() : null;
    $newsGrid = $featuredNews ? $newsItems->getCollection()->reject(fn ($item) => $item->is($featuredNews)) : $newsItems->getCollection();
    $featuredImage = $featuredNews?->thumbnail && Storage::disk('public')->exists($featuredNews->thumbnail)
        ? Storage::disk('public')->url($featuredNews->thumbnail)
        : null;
@endphp
<header class="public-news-hero">
    @if ($featuredImage)<img src="{{ $featuredImage }}" alt="" class="public-news-hero__image">@else<div class="public-news-hero__fallback" aria-hidden="true"></div>@endif
    <div class="public-news-hero__overlay" aria-hidden="true"></div>
    <div class="container public-news-hero__content">
        <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item active" aria-current="page">Berita</li></ol></nav>
        <p class="public-eyebrow">Kabar dan perkembangan</p>
        <h1>Berita Desa</h1>
        <p>Informasi terkini mengenai pemerintahan, pelayanan, pembangunan, dan kegiatan warga.</p>
    </div>
</header>

@if ($featuredNews)
<section class="container public-section public-featured-news" aria-labelledby="featured-news-title">
    <div class="public-featured-news__media">
        @if ($featuredImage)<img src="{{ $featuredImage }}" alt="{{ $featuredNews->title }}">@else<div class="public-news-card__placeholder" aria-hidden="true">Berita Desa</div>@endif
        <span class="public-category-label">{{ $featuredNews->category->name }}</span>
    </div>
    <div class="public-featured-news__copy">
        <p class="public-eyebrow">Headline terbaru</p>
        <h2 id="featured-news-title">{{ $featuredNews->title }}</h2>
        <p class="public-metadata"><x-admin.date-text :date="$featuredNews->published_at" /> <span aria-hidden="true">·</span> {{ number_format($featuredNews->views, 0, ',', '.') }} kali dilihat</p>
        <p class="public-featured-news__excerpt">{{ Str::limit($featuredNews->excerpt ?: $featuredNews->content, 280) }}</p>
        <a class="btn btn-primary" href="{{ route('news.show', $featuredNews->slug) }}">Baca berita utama<span aria-hidden="true"> →</span></a>
    </div>
</section>
@endif

<section class="public-section-wrap public-section-wrap--cream" aria-labelledby="news-list-heading">
<div class="container public-section">
    <x-public.section-heading id="news-list-heading" eyebrow="Arsip informasi" title="{{ $featuredNews ? 'Berita Lainnya' : 'Semua Berita' }}" description="Temukan kabar terbaru dari berbagai kegiatan dan layanan desa." />
    @if ($newsItems->isEmpty())
        <div class="public-empty-state"><h2 class="h5">Belum ada berita</h2><p class="mb-0">Berita yang telah diterbitkan akan tampil di halaman ini.</p></div>
    @elseif ($newsGrid->isNotEmpty())
        <div class="row g-4">@foreach ($newsGrid as $news)<div class="col-md-6 col-xl-4"><x-public.news-card :news="$news" /></div>@endforeach</div>
    @endif
    <div class="public-pagination">{{ $newsItems->links() }}</div>
</div>
</section>
@endsection
