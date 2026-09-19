@extends('layouts.admin')

@section('title', 'Tambah Kategori Berkas')

@section('content')
    <x-admin.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Kategori Berkas', 'url' => route('admin.document-categories.index')],
        ['label' => 'Tambah'],
    ]" />
    <x-admin.page-header title="Tambah Kategori Berkas" subtitle="Slug dibuat otomatis dari nama kategori." />
    <x-admin.card>
        @include('admin.document-categories._form', [
            'action' => route('admin.document-categories.store'),
            'documentCategory' => null,
            'submitLabel' => 'Simpan kategori',
        ])
    </x-admin.card>
@endsection
