@extends('layout')

@section('title')
    Categorías del servicio
@endsection


@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Categorías del servicio</h1>
            </div>
            <div class="col text-right">
                <a type="button" class="btn btn-primary" href="{{ route('categoryservices.create') }}"><span class="oi oi-plus" title="Nuevo" aria-hidden="true"></span> Crear Categoría</a>
            </div>
        </div>
        @if(session()->get('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        @endif

        <table id="myTable" class="table table-striped">
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
                        <td>
                            @if(!is_null($category->parent['name']))
                                {{$category->parent['name']}}
                            @endif
                        </td>
                        <td>
                            <a type="button" href="{{ route('categoryservices.edit', $category ) }}" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </a>
                            <form method="POST" action="{{ route('categoryservices.destroy', $category->id) }}" class="d-inline">
                                @method('DELETE')
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseas Eliminar la Categoría?')" data-toggle="tooltip" title='Eliminar'> <i class="oi oi-trash"> </i></button>
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
    @include('partials.structure.close-main')
@endsection