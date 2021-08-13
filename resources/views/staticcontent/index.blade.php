@extends('layout')

@section('title')
    Contenido estático
@endsection

@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Contenido estático</h1>
            </div>
            <div class="col text-right">
                <a type="button" class="btn btn-primary" href="{{ route('staticcontent.create') }}"><span class="oi oi-plus" title="Nuevo" aria-hidden="true"></span> @lang('Create') @lang('Content')</a>
            </div>
        </div>

        @include('partials.session-status')        
        <table id="myTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($staticContents as $staticContent)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $staticContent->title }}</td>
                        <td>{{ $staticContent->slug }}</td>
                        <td>
                            <a type="button" href="{{ route( 'staticcontent.edit', $staticContent ) }}" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </a>
                            <form method="POST" action="{{ route( 'staticcontent.destroy', $staticContent ) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas Eliminar el {{ __('Static content') }}?')" data-toggle="tooltip" title='Eliminar'> <i class="oi oi-trash"> </i></button>
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