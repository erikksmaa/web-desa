@extends('layouts.admin')
@section('title', 'Tambah Agenda')
@section('content')
<x-admin.breadcrumb :items="[['label'=>'Dashboard','url'=>route('admin.dashboard')],['label'=>'Agenda','url'=>route('admin.agendas.index')],['label'=>'Tambah']]" />
<x-admin.page-header title="Tambah Agenda" />
<x-admin.card>
@include('admin.agendas._form', ['action' => route('admin.agendas.store'), 'method' => 'POST'])
</x-admin.card>
@endsection
