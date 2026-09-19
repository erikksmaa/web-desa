@extends('layouts.public')
@section('title', 'Pengumuman — '.config('app.name'))
@section('content')
<x-public.page-header title="Pengumuman" eyebrow="Pemberitahuan resmi" description="Informasi resmi dan pemberitahuan terbaru dari pemerintah desa." variant="notices" />
<div class="container public-section">
<div class="row g-4">@forelse ($announcements as $item)<div class="col-lg-6"><x-public.announcement-card :item="$item" /></div>
@empty<div class="col-12"><p class="public-empty-state">Belum ada pengumuman aktif.</p></div>@endforelse</div>
<div class="public-pagination">{{ $announcements->links() }}</div>
</div>
@endsection
