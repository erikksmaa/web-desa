@extends('layouts.admin')

@section('title', 'Ubah Kategori Berita')

@section('content')
    <x-admin.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Kategori Berita', 'url' => route('admin.news-categories.index')],
        ['label' => 'Ubah'],
    ]" />
    <x-admin.page-header title="Ubah Kategori Berita" subtitle="Perubahan nama akan memperbarui slug kategori." />
    <x-admin.card>
        @include('admin.news-categories._form', [
            'action' => route('admin.news-categories.update', $newsCategory),
            'method' => 'PUT',
            'submitLabel' => 'Simpan perubahan',
        ])
    </x-admin.card>
@endsection
