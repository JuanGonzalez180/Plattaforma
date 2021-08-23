
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
    <hr>
    @include('partials.session-status')

    @if(session()->get('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
    @endif

    <table id="myTable" class="table table-striped table-bordered" style="width:100%">
        <thead>
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
                            <a type="button" href="{{ url('/licitaciones/'.$tender->id) }}" class="btn btn-outline-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
                            @if($tender->tendersVersionLast()->status == 'Publicada')
                                <form method="POST" action="{{ route( 'tender.decline') }}"  class="d-inline form-tender-decline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$tender->tendersVersionLast()->id}}"/>
                                    <button type="submit" class="btn btn-outline-danger btn-sm"> 
                                        <i class="fas fa-minus-circle"></i>
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-outline-secondary btn-sm" disabled='disabled'>
                                    <i class="fas fa-minus-circle"></i>
                                </button>
                            @endif
                            <button id="btnGroupDrop1" type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="fas fa-ellipsis-v" title="Ver" aria-hidden="true"></span>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                <a class="dropdown-item" href="{{ route('tender-companies-id', $tender->id ) }}">Compañias licitantes</a>
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
        $('.form-tender-decline').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: '¿Estas seguro?',
                text: "Deseas declinar la licitación?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor:  '#d33',
                confirmButtonText:  '¡Si, Declinar!',
                cancelButtonText:   'Cancelar'
            }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
            })
        });
    </script>

@endsection
