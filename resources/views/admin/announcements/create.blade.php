@extends('layouts.admin')
@section('title', 'Tambah Pengumuman')
@section('content')
<x-admin.breadcrumb :items="[['label'=>'Dashboard','url'=>route('admin.dashboard')],['label'=>'Pengumuman','url'=>route('admin.announcements.index')],['label'=>'Tambah']]" />
<x-admin.page-header title="Tambah Pengumuman" />
<x-admin.card>
@include('admin.announcements._form', ['action' => route('admin.announcements.store'), 'method' => 'POST'])
</x-admin.card>
@endsection
