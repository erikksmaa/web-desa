@extends('layouts.public')
@section('title', $announcement->title.' — '.config('app.name'))
@section('description', \Illuminate\Support\Str::limit($announcement->content, 155))
@section('content')
<article class="container public-article-container py-5">
<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">Pengumuman</a></li><li class="breadcrumb-item active" aria-current="page">Detail</li></ol></nav>
<p class="public-eyebrow">Pemberitahuan resmi</p>
<h1>{{ $announcement->title }}</h1>
<p class="text-muted">{{ \App\Support\IndonesianDate::format($announcement->published_at, true) }} WIB</p>
@if ($announcement->expires_at)<p>Berlaku hingga {{ \App\Support\IndonesianDate::format($announcement->expires_at, true) }} WIB</p>@endif
<div class="public-article-content public-plain-text public-reading-panel" >{{ $announcement->content }}</div>
@if ($announcement->attachment_path)<div class="public-attachment-panel mt-4"><a class="btn btn-primary" href="{{ route('announcements.download', $announcement->slug) }}">Unduh {{ $announcement->attachment_original_name ?: 'lampiran' }}</a></div>@endif
</article>
@endsection
