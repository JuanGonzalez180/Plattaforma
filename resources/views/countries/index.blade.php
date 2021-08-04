@extends('layout')

@section('title')
    Paises
@endsection

@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Paises</h1>
            </div>
            <div class="col text-right">
                <a type="button" class="btn btn-primary" href="{{ route('countries.create') }}"><span class="oi oi-plus" title="Nuevo" aria-hidden="true"></span> @lang('Create') @lang('Country')</a>
            </div>
        </div>

        @include('partials.session-status')        
        <table id="myTable" class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Código</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($countries as $country)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $country->name }}</td>
                        <td>{{ $country->slug }}</td>
                        <td>{{ $country->alpha2Code }}</td>
                        <td>
                            <a type="button" href="{{ route( 'countries.edit', $country ) }}" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </a>
                            <form method="POST" action="{{ route( 'countries.destroy', $country ) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas Eliminar el {{ __('Country') }}?')" data-toggle="tooltip" title='Eliminar'> <i class="oi oi-trash"> </i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No hay elementos</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @include('partials.structure.close-main')
@endsection