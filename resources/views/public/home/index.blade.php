@extends('layouts.public')
@section('title', 'Beranda — '.$siteSettings->get('site.name', config('app.name')))
@section('description', $siteSettings->get('site.tagline') ?: 'Informasi resmi, berita, agenda, dan publikasi desa.')
@section('content')
@include('public.home.hero')

@if ($villageHead || $siteSettings->get('village.head_welcome'))
<section class="public-home-welcome" aria-labelledby="welcome-title">
    <div class="container public-section">
        <div class="public-head-welcome">
            @if ($villageHead)
            <div class="public-head-portrait">
                <div class="public-head-photo-frame">
                @if ($villageHead->photo_path && Storage::disk('public')->exists($villageHead->photo_path))
                    <img src="{{ Storage::disk('public')->url($villageHead->photo_path) }}" class="public-official-photo" alt="{{ $villageHead->name }}" loading="lazy">
                @else
                    <div class="public-official-photo public-news-card__placeholder" role="img" aria-label="Foto kepala desa belum tersedia"><span aria-hidden="true">◎</span><small>Kepala Desa</small></div>
                @endif
                </div>
                <div class="public-head-signature"><p class="public-eyebrow mb-2">Kepala desa</p><p class="public-head-name">{{ $villageHead->name }}</p><p class="mb-0">{{ $villageHead->position }}</p></div>
            </div>
            @endif
            <div class="public-head-message">
                <p class="public-eyebrow">Sambutan pemerintah desa</p>
                <h2 id="welcome-title">Sambutan Kepala Desa</h2>
                <span class="public-heading-rule" aria-hidden="true"></span>
                @if ($text = $siteSettings->get('village.head_welcome'))<div class="public-plain-text public-welcome-text">{{ $text }}</div>@endif
                @if ($villageHead)<p class="public-welcome-attribution"><span>Hormat kami,</span><strong>{{ $villageHead->name }}</strong></p>@endif
                <a class="btn btn-outline-primary" href="{{ route('profile.officials') }}">Kenali perangkat desa<span aria-hidden="true"> →</span></a>
            </div>
        </div>
    </div>
</section>
@endif

<section class="public-section-wrap public-section-wrap--white" aria-labelledby="home-news">
    <div class="container public-section">
        <x-public.section-heading id="home-news" eyebrow="Kabar desa" title="Berita Terbaru" description="Ikuti kegiatan, pembangunan, dan cerita terbaru dari desa." :url="route('news.index')" />
        <div class="row g-4">@forelse ($newsItems as $news)<div class="col-md-6 col-xl-4"><x-public.news-card :news="$news" /></div>
        @empty<div class="col-12"><p class="public-empty-state">Belum ada berita terbaru.</p></div>@endforelse</div>
    </div>
</section>

<section class="public-section-wrap public-section-wrap--green" aria-labelledby="home-announcements">
    <div class="container public-section">
        <x-public.section-heading id="home-announcements" eyebrow="Informasi resmi" title="Pengumuman Desa" description="Pemberitahuan yang perlu diketahui masyarakat." :url="route('announcements.index')" />
        <div class="row g-4">@forelse ($announcements as $item)<div class="col-lg-6"><x-public.announcement-card :item="$item" :heading="3" /></div>
        @empty<div class="col-12"><p class="public-empty-state">Belum ada pengumuman terbaru.</p></div>@endforelse</div>
    </div>
</section>

<section class="public-section-wrap public-section-wrap--cream" aria-labelledby="home-agenda">
    <div class="container public-section">
        <x-public.section-heading id="home-agenda" eyebrow="Jadwal kegiatan" title="Agenda Mendatang" description="Catat waktu dan lokasi kegiatan desa." :url="route('agendas.index')" />
        <div class="row g-4">@forelse ($agendas as $item)<div class="col-lg-6"><x-public.agenda-card :item="$item" :compact="true" /></div>
        @empty<div class="col-12"><p class="public-empty-state">Belum ada agenda mendatang.</p></div>@endforelse</div>
    </div>
</section>

<section class="public-section-wrap public-section-wrap--white" aria-labelledby="home-documents">
    <div class="container public-section">
        <div class="public-documents-layout">
            <div class="public-documents-intro"><p class="public-eyebrow">Layanan informasi</p><h2 id="home-documents">Berkas Publik</h2><span class="public-heading-rule" aria-hidden="true"></span><p>Temukan dokumen, formulir, dan publikasi resmi desa yang dapat diunduh.</p><a class="btn btn-primary" href="{{ route('documents.index') }}">Lihat semua berkas</a></div>
            <div class="public-document-list">@forelse ($documents as $item)<x-public.document-item :item="$item" :compact="true" />@empty<p class="public-empty-state mb-0">Belum ada berkas publik.</p>@endforelse</div>
        </div>
    </div>
</section>

<section class="public-section-wrap public-section-wrap--dark" aria-labelledby="home-gallery">
    <div class="container public-section">
        <x-public.section-heading id="home-gallery" eyebrow="Cerita dalam gambar" title="Galeri Kegiatan" description="Dokumentasi kegiatan dan kebersamaan warga desa." :url="route('gallery.index')" />
        <div class="public-gallery-grid public-gallery-grid--home">@forelse ($albums as $album)<x-public.gallery-card :album="$album" :heading="3" />@empty<p class="public-empty-state">Belum ada galeri kegiatan.</p>@endforelse</div>
    </div>
</section>
@endsection
