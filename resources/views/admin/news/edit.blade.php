@extends('layouts.admin')

@section('title', 'Ubah Berita')

@section('content')
    <x-admin.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Berita', 'url' => route('admin.news.index')],
        ['label' => 'Ubah'],
    ]" />
    <x-admin.page-header title="Ubah Berita" subtitle="Perubahan judul akan memperbarui slug berita." />
    @include('admin.news._form', [
        'action' => route('admin.news.update', $news),
        'method' => 'PUT',
        'submitLabel' => 'Simpan perubahan',
    ])
@endsection
