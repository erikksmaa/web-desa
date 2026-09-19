@extends('layouts.admin')
@section('title', 'Tambah Album Galeri')
@section('content')
<x-admin.breadcrumb :items="[['label'=>'Dashboard','url'=>route('admin.dashboard')],['label'=>'Album Galeri','url'=>route('admin.gallery.index')],['label'=>'Tambah']]" />
<x-admin.page-header title="Tambah Album Galeri" />
<x-admin.card>
@include('admin.gallery._form', ['action' => route('admin.gallery.store'), 'method' => 'POST'])
</x-admin.card>
@endsection
