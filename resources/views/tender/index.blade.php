
@extends('layout')

@section('title')
    Licitaciones
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>Licitaciones</h1>
        </div>
    </div>
    @include('partials.session-status')
    <table id="myTable" class="display">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col">Responsable</th>
                <th scope="col">Proyecto</th>
                <th scope="col">Compañia</th>
                <th scope="col">Estado</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenders as $tender)
                <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{$tender->name}}</td>
                    <td>{{$tender->user->fullName()}}</td>
                    <td>{{$tender->project->name}}</td>
                    <td>{{$tender->company->name}}</td>
                    <td>{{$tender->tendersVersionLast()->status}}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <a type="button" href="{{ url('/licitaciones/'.$tender->id) }}" class="btn btn-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
                            <button id="btnGroupDrop1" type="button" class="btn btn-warning btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="oi oi-cog" title="Ver" aria-hidden="true"></span>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
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
