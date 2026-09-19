@extends('layouts.admin')
@section('title', 'Ubah Banner')
@section('content')
<x-admin.page-header title="Ubah Banner" />
<x-admin.card>
@include('admin.banners._form', ['action'=>route('admin.banners.update', $banner), 'method'=>'PATCH'])
</x-admin.card>
@endsection
