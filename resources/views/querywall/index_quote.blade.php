
@extends('layout')

@section('title')
    Licitaciones
@endsection

@section('content')
    @include('partials.structure.open-main')

    <div class="row align-items-center">
        <div class="col">
            <h1>Muro de consultas</h1>
        </div>
    </div>
    @if(session()->get('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
    @endif

    <hr>
    @include('partials.session-status')
    <table id="myTable" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Usuario</th>
                <th scope="col">Pregunta</th>
                <th scope="col">Respuesta</th>
                <th scope="col">Estado</th>
                <th scope="col">Visible</th>
            </tr>
        </thead>
        <tbody>
            @forelse($queryWalls as $queryWall)
                <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>
                        <p class="font-weight-normal">{{$queryWall->user->fullName()}}<br><strong>Compañia | {{$queryWall->company->name}}</strong></p>
                    </td>
                    <td>
                        <textarea class="form-control" rows="4" disabled>{{$queryWall->question}}</textarea>
                    </td>
                    <td>
                        <textarea class="form-control" rows="4" disabled>{{$queryWall->answer}}</textarea>
                    </td>
                    <td>
                        @if($queryWall->status == 'Respondido')
                            <span class="badge badge-primary">{{$queryWall->status}}</span>
                        @else
                            <span class="badge badge-success">{{$queryWall->status}}</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route( 'querywall.edit.visible') }}" class="d-inline form-wall-visible">
                            @csrf
                            <input type="hidden" name="id" value="{{$queryWall->id}}"/>
                            <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" @if($queryWall->visible == 'Visible') title='Visible' @else  title='No visible' @endif> 
                                @if($queryWall->visible == 'Visible')
                                    <i class="far fa-eye"></i>
                                @else
                                    <i class="far fa-eye-slash"></i>
                                @endif
                            </button>
                        </form>
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
        $('.form-wall-visible').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: '¿Estas seguro?',
                text: "Deseas cambiar el estado de la consulta?",
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
