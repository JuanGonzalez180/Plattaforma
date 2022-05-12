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
            <th scope="col">Tipo</th>
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
            <td>
                @switch($tender->type)

                @case('Publico')
                    <span class="badge badge-success">{{$tender->type}}</span>
                @break

                @case('Privado')
                    <span class="badge badge-danger">{{$tender->type}}</span>
                @break

                @default
                    <span class="badge badge-secondary">{{$tender->type}}</span>
                @endswitch
            </td>
            <td>{{$tender->user->fullName()}}</td>
            <td>{{$tender->project->name}}</td>
            <td>{{$tender->company->name}}</td>
            <td>
                @switch($tender->tendersVersionLast()->status)

                @case(in_array($tender->tendersVersionLast()->status, ['Publicada','Finalizada']))
                    <span class="badge badge-success">{{$tender->tendersVersionLast()->status}}</span>
                @break

                @case(in_array($tender->tendersVersionLast()->status, ['Borrador','Inactiva']))
                    <span class="badge badge-danger">{{$tender->tendersVersionLast()->status}}</span>
                @break

                @case(in_array($tender->tendersVersionLast()->status, ['Cerrada']))
                    <span class="badge badge-danger">{{$tender->tendersVersionLast()->status}}</span>
                @break

                @default
                    <span class="badge badge-secondary">{{$tender->tendersVersionLast()->status}}</span>
                @endswitch
            </td>
            <td>
                <div class="btn-group" role="group">
                    <a type="button" href="{{ url('/licitaciones/'.$tender->id) }}" class="btn btn-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
                    <button id="btnGroupDrop1" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="fas fa-ellipsis-v" title="Ver" aria-hidden="true"></span>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                        <a class="dropdown-item d-flex justify-content-between align-items-center @if(count($tender->tenderCompanies)<=0) disabled @endif" href="{{ route('tender-companies-id', $tender->id ) }}">
                            Compañias licitantes&nbsp;
                            <span class="badge badge-primary">{{count($tender->tenderCompanies)}}</span>
                        </a>
                        <a class="dropdown-item d-flex justify-content-between align-items-center @if(count($tender->querywalls)<=0) disabled @endif" href="{{ route('query.class.id', $tender->id ) }}">
                            Muro de consultas&nbsp;
                            <span class="badge badge-primary">{{count($tender->querywalls)}}</span>
                        </a>
                        <a class="dropdown-item d-flex justify-content-between align-items-center @if(count($tender->remarks)<=0) disabled @endif" href="{{ route('remark.class.id', ['tender',$tender->id] ) }}">
                            Reseñas&nbsp;
                            <span class="badge badge-primary">{{count($tender->remarks)}}</span>
                        </a>
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
    $('.form-tender-decline').submit(function(e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Estas seguro?',
            text: "Deseas declinar la licitación?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '¡Si, Declinar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        })
    });
</script>

@endsection