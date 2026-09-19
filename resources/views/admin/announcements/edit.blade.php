@extends('layouts.admin')
@section('title', 'Ubah Pengumuman')
@section('content')
<x-admin.breadcrumb :items="[['label'=>'Dashboard','url'=>route('admin.dashboard')],['label'=>'Pengumuman','url'=>route('admin.announcements.index')],['label'=>'Ubah']]" />
<x-admin.page-header title="Ubah Pengumuman" />
<x-admin.card>
@include('admin.announcements._form', ['action' => route('admin.announcements.update', $announcement), 'method' => 'PUT'])
</x-admin.card>
@endsection
