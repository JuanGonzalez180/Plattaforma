
@extends('layout')

@section('title')
    Equipo
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>Equipo</h1>
        </div>
    </div>
    <hr>
    @include('partials.session-status')
    <table id="myTable" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Usuario</th>
                <th scope="col">Compañia</th>
                <th scope="col">Posición</th>
                <th scope="col">Estado</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($teams as $team)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>
                    {{$team->user->username}}&nbsp;
                    @if($team->user->isAdmin())
                        <span class="badge badge-warning">Administrador</span>
                    @endif
                </td>   
                <td>{{$team->company->name}}</td>  
                <td class="text-center">
                    @if($team->position)
                        {{$team->position}}
                    @else
                        <span class="badge badge-secondary">sin definir</span>
                    @endif
                </td>  
                <td class="text-center">
                @if($team->status == 'Aprobado')
                    <span class="badge badge-success">{{$team->status}}</span>
                @else
                    <form method="POST" action="{{ route('teams.edit.status') }}" class="d-inline form-team-approve">
                        @csrf
                        <input type="hidden" name="id" value="{{$team->id}}"/>
                        <input type="hidden" name="status" value="Aprobado"/>
                        <button type="submit" class="btn btn-success btn-sm" data-toggle="tooltip" title='Aprobar'><i class="far fa-thumbs-up"></i></button>
                    </form>
                @endif
                </td>  
                <td>
                    <a type="button" href="{{ route('team.show', $team->id ) }}" class="btn btn-success btn-sm">
                        <span class="oi oi-eye" title="Ver" aria-hidden="true"></span>
                    </a>
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
        $('.form-team-approve').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: '¿Estas seguro?',
                text: "El usuario estara aprobado",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor:  '#d33',
                confirmButtonText:  '¡Si, Aprobar!',
                cancelButtonText:   'Cancelar'
            }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
            })
        });
    </script>
@endsection
