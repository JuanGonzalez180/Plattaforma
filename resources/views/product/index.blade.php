@extends('layout')

@section('title')
    Categorías
@endsection


@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Productos</h1>
            </div>
        </div>
        <hr>
        @if(session()->get('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        @endif
        <table id="product_table" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Compañia</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Marca</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Tamaño</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
        </table>

        
    @include('partials.structure.close-main')
    <script>
        var table;
        $(document).ready(function(){      
            table = $('#product_table').DataTable( {
                "serverSide": true,
                "ordering": false,
                "ajax": {
                    "url": "{{ route('company.products') }}",
                    "type": "POST",
                    "headers": {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    "data": function(d){
                        d.company_id ={{ $company_id }};
                    }
                },
                "columns" : [
                    {data: 'id'},
                    {data: 'name'},
                    {data: 'company_id'},
                    {data: 'user_id'},
                    {data: 'brand_id'},
                    {data: 'type'},
                    {data: 'size_product'},
                    {data: 'status'},
                    {data: 'actions'},
                ],
                "lengthMenu": [
                    [20, 50, 100],
                    [20, 50, 100]
                ],
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros por pagina",
                    "zeroRecords": "Nothing found - sorry",
                    "info": "Mostrando la pagina _PAGE_ de _PAGES_",
                    "infoEmpty": "No hay elementos",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "search": "Buscar:",
                    "paginate":{
                        "next":"Siguiente",
                        "previous":"Anterior"
                    }
                }
            } );
        });
    </script>
@endsection

