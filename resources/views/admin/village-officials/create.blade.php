@extends('layouts.admin')
@section('title', 'Tambah Perangkat Desa')
@section('content')
<x-admin.page-header title="Tambah Perangkat Desa" />
<x-admin.card>
@include('admin.village-officials._form', ['action'=>route('admin.village-officials.store'), 'method'=>'POST'])
</x-admin.card>
@endsection
