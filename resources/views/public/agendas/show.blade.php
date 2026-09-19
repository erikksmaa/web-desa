@extends('layouts.public')
@section('title', $agenda->title.' — '.config('app.name'))
@section('description', \Illuminate\Support\Str::limit($agenda->description,155))
@section('content')
<article class="container public-article-container py-5">
<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item"><a href="{{ route('agendas.index') }}">Agenda</a></li><li class="breadcrumb-item active" aria-current="page">Detail</li></ol></nav>
<p class="public-eyebrow">Agenda kegiatan</p>
<h1>{{ $agenda->title }}</h1>
<dl class="public-event-details mt-4"><dt>Mulai</dt><dd>{{ \App\Support\IndonesianDate::format($agenda->start_at,true) }} WIB</dd>
@if ($agenda->end_at)<dt>Selesai</dt><dd>{{ \App\Support\IndonesianDate::format($agenda->end_at,true) }} WIB</dd>@endif
@if ($agenda->location)<dt>Lokasi</dt><dd>{{ $agenda->location }}</dd>@endif</dl>
<div class="public-article-content public-plain-text public-reading-text" >{{ $agenda->description }}</div>
</article>
@endsection
