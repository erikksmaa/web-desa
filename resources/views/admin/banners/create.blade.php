@extends('layouts.admin')
@section('title', 'Tambah Banner')
@section('content')
<x-admin.page-header title="Tambah Banner" />
<x-admin.card>
@include('admin.banners._form', ['action'=>route('admin.banners.store'), 'method'=>'POST'])
</x-admin.card>
@endsection
