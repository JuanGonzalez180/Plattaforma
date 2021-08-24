@extends('layout')

@section('title')
    Tipos de Entidad
@endsection

@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Tipos de Entidad</h1>
            </div>
            <div class="col text-right">
                <a type="button" class="btn btn-primary btn-sm" href="{{ route('typesentity.create') }}"><i class="fas fa-plus"></i>&nbsp;Crear Tipo de Entidad</a>
            </div>
        </div>
        <hr>
        @include('partials.session-status')
        <table id="myTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($typesEntity as $typeEntity)
                    <tr>
                        <td scope="row">{{ $loop->iteration }}</td>
                        <td>{{ $typeEntity->name }}</td>
                        <td>{{ $typeEntity->type->name }}</td>
                        <td>{{ $typeEntity->status }}</td>
                        <td>
                            <a type="button" href="{{ route( 'typesentity.edit', $typeEntity ) }}" class="btn btn-dark btn-sm"><i class="fas fa-pencil-alt"></i></a>
                            <form method="POST" action="{{ route( 'typesentity.destroy', $typeEntity ) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas Eliminar el {{ __('Entity type') }}?')" data-toggle="tooltip" title='Eliminar'><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    @include('partials.structure.close-main')
@endsection