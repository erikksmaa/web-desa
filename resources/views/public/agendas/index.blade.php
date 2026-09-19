@extends('layouts.public')
@section('title', 'Agenda — '.config('app.name'))
@section('content')
<x-public.page-header title="Agenda Desa" eyebrow="Kalender kegiatan" description="Jadwal kegiatan dan pertemuan desa. Semua waktu dalam WIB." variant="agenda" />
<div class="container public-section">
@foreach (['Agenda Mendatang / Berlangsung'=>$upcoming, 'Agenda Sebelumnya'=>$past] as $heading=>$items)
<section @class(['mb-5','public-past-agendas'=>$loop->last])><div class="public-section-heading"><h2 class="h3 mb-0">{{ $heading }}</h2></div>
<div class="row g-3">@forelse ($items as $item)<div class="col-lg-6"><x-public.agenda-card :item="$item" :past="$heading === 'Agenda Sebelumnya'" /></div>
@empty<div class="col-12"><p class="public-empty-state">Belum ada agenda pada bagian ini.</p></div>@endforelse</div>
<div class="public-pagination">{{ $items->links() }}</div>
</section>
@endforeach
</div>
@endsection
