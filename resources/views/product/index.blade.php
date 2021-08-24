
@extends('layout')

@section('title')
    Productos
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>Productos</h1>
        </div>
    </div>
    <hr>
    @include('partials.session-status')
    <table id="myTable" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col">Compañia</th>
                <th scope="col">Usuario</th>
                <th scope="col">Marca</th>
                <th scope="col">Tipo</th>
                <th scope="col">Estado</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$product->name}}</td>
                <td>{{$product->company->name}}</td>
                <td>{{$product->user->username}}</td>
                <td>{{$product->brand->name}}</td>
                <td>{{$product->type}}</td>
                <td>{{$product->status}}</td>
                <td>

                <div class="btn-group" role="group">
                    <a type="button" href="{{ route('productos.show', $product->id) }}" class="btn btn-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
                </div>

                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="8">No hay elementos</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @include('partials.structure.close-main')
@endsection


