@extends('layouts.admin')

@section('title', 'Ubah Kategori Berkas')

@section('content')
    <x-admin.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Kategori Berkas', 'url' => route('admin.document-categories.index')],
        ['label' => 'Ubah'],
    ]" />
    <x-admin.page-header title="Ubah Kategori Berkas" subtitle="Perubahan nama akan memperbarui slug kategori." />
    <x-admin.card>
        @include('admin.document-categories._form', [
            'action' => route('admin.document-categories.update', $documentCategory),
            'method' => 'PUT',
            'submitLabel' => 'Simpan perubahan',
        ])
    </x-admin.card>
@endsection
