@extends('layout')

@section('title')
    Tipos de Entidad
@endsection

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
        <h1>Tipos de Entidad</h1>
        <a href="{{ route('typesentity.create') }}" class="btn btn-primary btn-sm">Crear</a><br><br>
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
                @forelse ($typesEntity as $entity)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $entity->name }}</td>
                        <td>{{ $entity->type->name }}</td>
                        <td>
                            <button type="button" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </button>
                            <button type="button" class="btn btn-danger btn-sm"> <span class="oi oi-trash" title="Eliminar" aria-hidden="true"></span> </button>
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