@extends('layout')

@section('title')
Licitaciones
@endsection

@section('content')
@include('partials.structure.open-main')
<div class="row align-items-center">
    <div class="col">
        <h1>Correos de invitación</h1>
    </div>
</div>
<hr>
<div class="container">
    <div class="row">
        <div class="col-sm">
            <label for="company">Licitaciones</label>
            <select name="tender" id="tender" class="form-control form-control-sm">
                <option value="all">Todas</option>
                @foreach($tenders as $value)
                <option value="{{$value['id']}}">{{ucfirst($value['name'])}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm">
            <label for="status">Compañia</label>
            <select name="company" id="company" class="form-control form-control-sm">
                <option value="all">Todas</option>
                @foreach($companies as $value)
                <option value="{{$value['id']}}">{{ucfirst($value['name'])}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<br>
@include('partials.session-status')
<table class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Correo</th>
            <th scope="col">Licitación</th>
            <th scope="col">Compañia</th>
            <th scope="col">Registrado</th>
            <th scope="col">Fecha</th>
        </tr>
    </thead>
</table>
@include('partials.structure.close-main')
<script>

</script>
@endsection