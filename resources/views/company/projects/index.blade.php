@extends('layout')

@section('title')
Proveedores
@endsection


@section('content')
@include('partials.structure.open-main')
<div class="row align-items-center">
    <div class="col">
        <h2>Compañias</h2>
    </div>
</div>
<hr>
@if(session()->get('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif
<div class="form-group col-md-6">
    <label for="parent_id">Estado</label>
    <select name="status" id="status" class="form-control form-control-sm">
        <option value="all">Todos</option>
        @foreach($status as $value)
        <option value="{{$value}}">{{$value}}</option>
        @endforeach
    </select>
</div>
@include('partials.structure.close-main')

@endsection