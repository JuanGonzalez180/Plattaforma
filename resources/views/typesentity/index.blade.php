@extends('layout')

@section('title')
    Tipos de Entidad
@endsection

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
        <div class="row align-items-center">
            <div class="col">
                <h1>Tipos de Entidad</h1>
            </div>
            <div class="col text-right">
                <a type="button" class="btn btn-primary" href="{{ route('typesentity.create') }}"><span class="oi oi-new" title="Nuevo" aria-hidden="true"></span> Crear Categoría</a>
            </div>
        </div>

        @include('partials.session-status')        
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($typesEntity as $typeEntity)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $typeEntity->name }}</td>
                        <td>{{ $typeEntity->type->name }}</td>
                        <td>
                            <a type="button" href="{{ route( 'typesentity.edit', $typeEntity ) }}" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </a>
                            <form method="POST" action="{{ route( 'typesentity.destroy', $typeEntity ) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas Eliminar el Tipo de Entidad?')" data-toggle="tooltip" title='Eliminar'> <i class="oi oi-trash"> </i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No hay elementos</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>
@endsection