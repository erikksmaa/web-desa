@extends('layouts.public')
@section('title', 'Profil Desa — '.$siteSettings->get('site.name', config('app.name')))
@section('description', 'Visi, misi, dan sejarah desa.')
@section('content')
<x-public.page-header title="Profil Desa" :eyebrow="$siteSettings->get('site.name', config('app.name'))" :description="$siteSettings->get('site.tagline') ?: 'Mengenal arah pembangunan, perjalanan, dan pemerintahan desa.'" variant="profile" />

<nav class="public-profile-nav-wrap" aria-label="Bagian profil desa"><div class="container public-profile-nav"><a href="#visi">Visi</a><a href="#misi">Misi</a><a href="#sejarah">Sejarah</a><a href="{{ route('profile.organization') }}">SOTK</a><a href="{{ route('profile.officials') }}">Perangkat Desa</a></div></nav>

<section class="container public-section public-profile-direction" aria-label="Arah pembangunan desa">
    <div id="visi" class="public-vision-panel">
        <p class="public-eyebrow">Arah bersama</p>
        <h2>Visi Desa</h2>
        <span class="public-quote-mark" aria-hidden="true">“</span>
        @if ($text = $siteSettings->get('village.vision'))<div class="public-plain-text public-vision-text">{{ $text }}</div>@else<p class="mb-0">Informasi visi belum tersedia.</p>@endif
    </div>
    <div id="misi" class="public-mission-panel">
        <p class="public-eyebrow">Langkah mewujudkan visi</p>
        <h2>Misi Desa</h2>
        <span class="public-heading-rule" aria-hidden="true"></span>
        @if ($text = $siteSettings->get('village.mission'))<div class="public-plain-text public-mission-text">{{ $text }}</div>@else<p class="text-muted mb-0">Informasi misi belum tersedia.</p>@endif
    </div>
</section>

<section class="public-section-wrap public-section-wrap--cream" id="sejarah" aria-labelledby="history-title">
    <div class="container public-section public-history-layout">
        <div class="public-history-heading"><p class="public-eyebrow">Jejak perjalanan</p><h2 id="history-title">Sejarah Desa</h2><span class="public-heading-rule" aria-hidden="true"></span><p>Mengenal akar, perkembangan, dan semangat masyarakat desa dari masa ke masa.</p></div>
        <div class="public-history-copy">@if ($text = $siteSettings->get('village.history'))<div class="public-plain-text public-reading-text">{{ $text }}</div>@else<p class="text-muted">Informasi sejarah desa belum tersedia.</p>@endif</div>
    </div>
</section>

<section class="container public-section" aria-labelledby="profile-government-title">
    <x-public.section-heading id="profile-government-title" eyebrow="Pemerintahan desa" title="Kenali Struktur dan Pelayan Desa" description="Lihat susunan organisasi serta perangkat yang melayani masyarakat." />
    <div class="public-profile-links">
        <a href="{{ route('profile.organization') }}"><span class="public-profile-links__number">01</span><span><strong>Struktur Organisasi</strong><small>Bagan SOTK pemerintah desa</small></span><span aria-hidden="true">→</span></a>
        <a href="{{ route('profile.officials') }}"><span class="public-profile-links__number">02</span><span><strong>Perangkat Desa</strong><small>Nama, jabatan, dan profil perangkat</small></span><span aria-hidden="true">→</span></a>
    </div>
</section>
@endsection
