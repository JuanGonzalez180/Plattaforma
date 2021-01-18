@extends('layout')

@section('title')
    Index
@endsection

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
        <h1>Categorías</h1>
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
                <tr>
                    <th scope="row">1</th>
                    <td>Mark</td>
                    <td>Otto</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm"> <span class="oi oi-eye" title="Eliminar" aria-hidden="true"></span> </button>
                        <button type="button" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </button>
                        <button type="button" class="btn btn-danger btn-sm"> <span class="oi oi-trash" title="Eliminar" aria-hidden="true"></span> </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>Jacob</td>
                    <td>Thornton</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm"> <span class="oi oi-eye" title="Eliminar" aria-hidden="true"></span> </button>
                        <button type="button" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </button>
                        <button type="button" class="btn btn-danger btn-sm"> <span class="oi oi-trash" title="Eliminar" aria-hidden="true"></span> </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">3</th>
                    <td>Larry</td>
                    <td>the Bird</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm"> <span class="oi oi-eye" title="Eliminar" aria-hidden="true"></span> </button>
                        <button type="button" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </button>
                        <button type="button" class="btn btn-danger btn-sm"> <span class="oi oi-trash" title="Eliminar" aria-hidden="true"></span> </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </main>
@endsection