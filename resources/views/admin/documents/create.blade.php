@extends('layouts.admin')
@section('title', 'Tambah Berkas')
@section('content')
<x-admin.breadcrumb :items="[['label'=>'Dashboard','url'=>route('admin.dashboard')],['label'=>'Berkas','url'=>route('admin.documents.index')],['label'=>'Tambah']]" />
<x-admin.page-header title="Tambah Berkas" />
<x-admin.card>
@include('admin.documents._form', ['action' => route('admin.documents.store'), 'method' => 'POST'])
</x-admin.card>
@endsection
