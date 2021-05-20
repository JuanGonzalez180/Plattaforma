@extends('layout')

@section('title')
    Marcas
@endsection


@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Marcas</h1>
            </div>
            <div class="col text-right">
                <a type="button" class="btn btn-primary" href="{{ route('brand.create') }}"><span class="oi oi-plus" title="Nuevo" aria-hidden="true"></span> Crear Marca</a>
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
                    <th scope="col">Marca</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($brands as $brand)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $brand->name }}</td>
                        <td>
                            <a type="button" href="{{ route('brand.edit', $brand ) }}" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </a>
                            
                            @if ( $brand->id != 1 )
                                <form method="POST" action="{{ route('brand.destroy', $brand->id) }}" class="d-inline">
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseas cambiar el estado de la Marca?')" data-toggle="tooltip" title='Eliminar'> 
                                        @if ($brand->status == $enabled)
                                            <i class="fas fa-eye"> </i> 
                                        @else
                                            <i class="fas fa-eye-slash"> </i> 
                                        @endif
                                    </button>
                                </form>
                            @endif
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