
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
    <hr>
    @include('partials.session-status')
    <table id="myTable" class="table table-striped">
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
                        <a type="button" href="{{ route('companies.show', $company->id ) }}" class="btn btn-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
                        <!--<a type="button" href="" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Ver" aria-hidden="true"></span> </a>-->
                        <button id="btnGroupDrop1" type="button" class="btn btn-warning btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="fas fa-ellipsis-v" title="Ver" aria-hidden="true"></span>
                        </button>
                        @if($company->type_entity->type->name == 'Demanda')
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <a class="dropdown-item" href="{{ route('project-company-id', $company->id ) }}">Proyectos</a>
                            <a class="dropdown-item" href="{{ route('tender-company-id', ['company',$company->id] ) }}">Licitaciones</a>
                        </div>
                        @else
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <a class="dropdown-item" href="{{ route('product-company-id', ['product', $company->id] ) }}">Productos</a>
                            <a class="dropdown-item" href="{{ route('product-company-id', ['service', $company->id] ) }}">Servicios</a>
                            <a class="dropdown-item" href="{{ route('company-brand-id', $company->id ) }}">Marca</a>
                        </div>
                        @endif
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
