@extends('layouts.admin')
@section('title', 'Ubah Berkas')
@section('content')
<x-admin.breadcrumb :items="[['label'=>'Dashboard','url'=>route('admin.dashboard')],['label'=>'Berkas','url'=>route('admin.documents.index')],['label'=>'Ubah']]" />
<x-admin.page-header title="Ubah Berkas" />
<x-admin.card>
@include('admin.documents._form', ['action' => route('admin.documents.update', $document), 'method' => 'PUT'])
</x-admin.card>
@endsection
