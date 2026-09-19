@extends('layouts.admin')

@section('title', 'Tambah Kategori Berita')

@section('content')
    <x-admin.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Kategori Berita', 'url' => route('admin.news-categories.index')],
        ['label' => 'Tambah'],
    ]" />
    <x-admin.page-header title="Tambah Kategori Berita" subtitle="Slug dibuat otomatis dari nama kategori." />
    <x-admin.card>
        @include('admin.news-categories._form', [
            'action' => route('admin.news-categories.store'),
            'newsCategory' => null,
            'submitLabel' => 'Simpan kategori',
        ])
    </x-admin.card>
@endsection
