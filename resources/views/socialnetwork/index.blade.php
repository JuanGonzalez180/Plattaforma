@extends('layout')

@section('title')
    Redes Sociales
@endsection


@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Redes Sociales</h1>
            </div>
            <div class="col text-right">
                <a type="button" class="btn btn-primary" href="{{ route('socialnetwork.create') }}"><span class="oi oi-plus" title="Nuevo" aria-hidden="true"></span> Crear Item</a>
            </div>
        </div>
        @if(session()->get('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        @endif

        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($socialnetworks as $socialnetwork)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $socialnetwork->name }}</td>
                        <td>
                            <a type="button" href="{{ route('socialnetwork.edit', $socialnetwork ) }}" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </a>
                            <form method="POST" action="{{ route('socialnetwork.destroy', $socialnetwork->id) }}" class="d-inline">
                                @method('DELETE')
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseas Eliminar la Red Social?')" data-toggle="tooltip" title='Eliminar'> <i class="oi oi-trash"> </i></button>
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