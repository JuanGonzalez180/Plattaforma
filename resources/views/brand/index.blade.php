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
                <a type="button" class="btn btn-primary btn-sm" href="{{ route('brand.create') }}"><i class="fas fa-plus"></i>&nbsp;Crear Marca</a>
            </div>
        </div>
        @if(session()->get('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        @endif

        <hr>

        <table id="myTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Marca</th>
                    <th scope="col">Compañia</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($brands as $brand)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $brand->name }}</td>
                        <td>
                            @if ($brand->company)
                                {{ $brand->company['name'] }}
                            @endif
                        </td>
                        <td>
                            <a type="button" href="{{ route('brand.edit', $brand ) }}" class="btn btn-dark btn-sm"><i class="fas fa-pencil-alt"></i></a>
                            
                            @if ( $brand->id != 1 )
                                <form method="POST" action="{{ route('brand.destroy', $brand->id) }}" class="d-inline form-brand-status">
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" @if($brand->status == $enabled) title='Visible' @else  title='No visible' @endif> 
                                        @if($brand->status == $enabled)
                                            <i class="far fa-eye"></i>
                                        @else
                                            <i class="far fa-eye-slash"></i>
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
    <script>
        $('.form-brand-status').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: '¿Estas seguro?',
                text: "Deseas cambiar el estado de la Marca?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor:  '#d33',
                confirmButtonText:  '¡Si, Cambiar!',
                cancelButtonText:   'Cancelar'
            }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
            })
        });
    </script>
@endsection