@extends('layouts.admin')

@section('title', 'Tambah Berita')

@section('content')
    <x-admin.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Berita', 'url' => route('admin.news.index')],
        ['label' => 'Tambah'],
    ]" />
    <x-admin.page-header title="Tambah Berita" subtitle="Tulis berita sebagai teks biasa dan tentukan status publikasinya." />
    @include('admin.news._form', [
        'action' => route('admin.news.store'),
        'news' => null,
        'submitLabel' => 'Simpan berita',
    ])
@endsection
