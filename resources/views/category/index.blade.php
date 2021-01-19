@extends('layout')

@section('title')
    Categorías
@endsection


@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
        <div class="row align-items-center">
            <div class="col">
                <h1>Categorías</h1>
            </div>
            <div class="col text-right">
                <a type="button" class="btn btn-primary" href="{{ route('category.create') }}"><span class="oi oi-new" title="Nuevo" aria-hidden="true"></span> Crear Categoría</a>
            </div>
        </div>
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Categoría</th>
                    <th scope="col">Padre</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->parent['name'] }}</td>
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