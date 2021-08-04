
@extends('layout')

@section('title')
    Compañias
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>Proyectos</h1>
        </div>
    </div>
    <hr>
    @include('partials.session-status')
    <table id="myTable" class="table table-striped">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col">Usuario Responsable</th>
                <th scope="col">Compañia</th>
                <th scope="col">Estado</th>
                <th scope="col">Visible</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $project)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$project->name}}</td>
                <td>{{$project->user->name}}</td>
                <td>{{$project->company->name}}</td>
                <td>
                    @if($project->status == 'especificaciones-tecnicas')
                        <span class="badge badge-warning">{{ $project->status }}</span>
                    @else
                        <span class="badge badge-danger">{{ $project->status }}</span>
                    @endif
                </td>
                <td></td>
                <td>
                    <div class="btn-group" role="group">
                        <a type="button" href="{{ url('/proyecto/'.$project->id) }}" class="btn btn-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
                        <button id="btnGroupDrop1" type="button" class="btn btn-warning btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="fas fa-ellipsis-v" title="Ver" aria-hidden="true"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <a class="dropdown-item" href="{{ url('/tender/project/'.$project->id) }}">Licitaciones</a>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="7">No hay elementos</td>
                </tr>
            @endforelse
            
        </tbody>
    </table>
    @include('partials.structure.close-main')
@endsection
