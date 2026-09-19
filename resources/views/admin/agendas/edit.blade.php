@extends('layouts.admin')
@section('title', 'Ubah Agenda')
@section('content')
<x-admin.breadcrumb :items="[['label'=>'Dashboard','url'=>route('admin.dashboard')],['label'=>'Agenda','url'=>route('admin.agendas.index')],['label'=>'Ubah']]" />
<x-admin.page-header title="Ubah Agenda" />
<x-admin.card>
@include('admin.agendas._form', ['action' => route('admin.agendas.update', $agenda), 'method' => 'PUT'])
</x-admin.card>
@endsection
