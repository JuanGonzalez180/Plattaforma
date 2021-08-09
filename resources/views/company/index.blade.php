
@extends('layout')

@section('title')
    @if($type == 'Demanda')
        Compañias tipo demanda
    @else    
        Compañias tipo oferta
    @endif
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>
            @if($type == 'Demanda')
                Compañias tipo demanda
            @else    
                Compañias tipo Oferta
            @endif
            </h1>
        </div>
    </div>
    <hr>
    @include('partials.session-status')

    @if(session()->get('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
    @endif

    <table id="myTable" class="table table-striped">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col">Entidad</th>
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
                <td>
                    @if($company && $company->status == 'Creado' )
                    <form method="POST" action="{{ route( 'users.approve', $company->user ) }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="id" value="{{$company->id}}"/>
                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Deseas aprobar el usuario?')" data-toggle="tooltip" title='Abrobar'><i class="fas fa-check"></i></i></button>
                    </form>
                    <form method="POST" action="{{ route( 'users.disapproved', $company->user ) }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="id" value="{{$company->id}}"/>
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas rechazar el usuario?')" data-toggle="tooltip" title='Rechazar'><i class="fas fa-times"></i></button>
                    </form>
                    @elseif($company && $company->status == 'Aprobado' )
                        <span class="badge badge-success"><i class="fas fa-check"></i> {{$company->status}}</span>
                    @else
                        <span class="badge badge-danger"><i class="fas fa-times"></i> {{$company->status}}</span>
                    @endif
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <a type="button" href="{{ route('companies.show', $company->id ) }}" class="btn btn-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
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
