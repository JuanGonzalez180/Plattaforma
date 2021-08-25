
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
    <table id="myTable" class="table table-striped table-bordered" style="width:100%">
        <thead>
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
                <td>{{$project->user->username}}</td>
                <td>{{$project->company->name}}</td>
                <td>
                    @if($project->status == 'especificaciones-tecnicas')
                        <span class="badge badge-warning">{{ $project->status }}</span>
                    @else
                        <span class="badge badge-danger">{{ $project->status }}</span>
                    @endif
                </td>
                <td>
                <form method="POST" action="{{ route( 'project.edit.visible') }}" class="d-inline form-project-status">
                    @csrf
                    <input type="hidden" name="id" value="{{$project->id}}"/>
                    <button type="submit" class="btn btn-link btn-sm"> 
                        @if ($project->visible == $visible)
                            <i class="far fa-eye" data-toggle="tooltip" title='Visible'></i>
                        @else
                            <i class="far fa-eye-slash" data-toggle="tooltip" title='No visible'></i>
                        @endif
                    </button>
                </form>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <a type="button" href="{{ route('project.show', $project->id ) }}" class="btn btn-outline-success btn-sm">
                            <span class="oi oi-eye" title="Ver" aria-hidden="true"></span>
                        </a>
                        <button id="btnGroupDrop1" type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="fas fa-ellipsis-v" title="Ver" aria-hidden="true"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <a class="dropdown-item" href="{{ route('tender-company-id', ['project',$project->id] ) }}">Licitaciones</a>
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
    <script>
        $('.form-project-status').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: '¿Estas seguro?',
                text: "Deseas cambiar el estado del proyecto?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor:  '#d33',
                confirmButtonText:  '¡Si, Cambiar!',
                cancelButtonText:   'Cancelar'
            }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
            })
        });
    </script>
@endsection
