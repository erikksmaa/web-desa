@extends('layouts.admin')
@section('title', 'Ubah Perangkat Desa')
@section('content')
<x-admin.page-header title="Ubah Perangkat Desa" />
<x-admin.card>
@include('admin.village-officials._form', ['action'=>route('admin.village-officials.update', $official), 'method'=>'PATCH'])
</x-admin.card>
@endsection
