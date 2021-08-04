
@extends('layout')

@section('title')
    Compañias
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>Compañias</h1>
        </div>
    </div>
    @include('partials.session-status')
    <table id="myTable" class="display">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col">Entidad</th>
                <th scope="col">Tipo</th>
                <th scope="col">Estado</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($companies as $company)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$company->name}}</td>
                <td>{{$company->type_entity->name}}</td>
                <td>{{$company->type_entity->type->name}}</td>
                <td>{{$company->status}}</td>
                <td>
                    <div class="btn-group" role="group">
                        <a type="button" href="{{ url('/companias/'.$company->id) }}" class="btn btn-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
                        <a type="button" href="" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Ver" aria-hidden="true"></span> </a>
                        <button id="btnGroupDrop1" type="button" class="btn btn-warning btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="oi oi-cog" title="Ver" aria-hidden="true"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <a class="dropdown-item" href="#">Usuarios</a>
                            <a class="dropdown-item" href="{{ url('/project/company/'.$company->id) }}">Proyectos</a>
                            <a class="dropdown-item" href="{{ url('/tender/company/'.$company->id) }}">Licitaciones</a>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="6">No hay elementos</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @include('partials.structure.close-main')
@endsection
