@extends('layouts.public')
@section('title', 'SOTK — '.$siteSettings->get('site.name', config('app.name')))
@section('description', 'Struktur organisasi dan tata kerja pemerintah desa.')
@section('content')
<x-public.page-header title="Struktur Organisasi dan Tata Kerja" eyebrow="Pemerintah desa" :description="'Susunan organisasi pemerintahan '.$siteSettings->get('site.name', config('app.name')).'.'" variant="profile" />
<div class="container public-section">
<div class="public-sotk-intro"><p class="public-eyebrow">Bagan pemerintahan</p><h2>Struktur pelayanan yang jelas</h2><p>Diagram berikut menunjukkan hubungan kerja dan tanggung jawab pemerintah desa.</p></div>
<div class="public-organization-panel">
@if ($image = $siteSettings->image('sotk.image'))
<a href="{{ $image }}" aria-label="Buka gambar struktur organisasi ukuran penuh"><img src="{{ $image }}" class="img-fluid w-100 public-organization" alt="Bagan struktur organisasi pemerintah {{ $siteSettings->get('site.name', config('app.name')) }}"></a>
<p class="small text-muted mt-3 mb-0">Pilih gambar untuk melihat ukuran penuh. <a href="{{ route('profile.officials') }}">Lihat nama dan jabatan perangkat desa</a>.</p>
@else
<div class="public-empty-state"><p class="mb-0 text-muted">Struktur organisasi belum tersedia.</p></div>
@endif
</div>
</div>
@endsection
