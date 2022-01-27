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
                <a type="button" class="btn btn-primary btn-sm" href="{{ route('categoryservices.create') }}"><i class="fas fa-plus"></i>&nbsp;Crear Categoría</a>
            </div>
        </div>
        @if(session()->get('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        @endif
        
        <div class="form-group col-md-6">
            <label for="parent_id">Categorias padre</label>
            <select name="parent_id" id="parent_id" class="form-control form-control-sm" onchange="getCategoryChilds(this.value);">
                <option value="all">TODAS LAS CATEGORIAS DEL SERVICIO</option>
            @foreach($parents as $parent)
                <option value="{{$parent->id}}">{{$parent->name}}</option>
            @endforeach
            </select>
        </div>

        <table id="category_table" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Categoria</th>
                    <th scope="col">Padre</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
        </table>
    @include('partials.structure.close-main')
    <script>
        var table;
        $(document).ready(function(){      
            table = $('#category_table').DataTable( {
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('category.services.childs') }}",
                    "type": "POST",
                    "headers": {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    "data": function(d){
                        d.parent_id = getParentId();
                    }
                },
                "columns" : [
                    {data: 'id'},
                    {data: 'name'},
                    {data: 'parent_id'},
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

        function getParentId()
        {
            var select = document.getElementById('parent_id');
            var value = select.options[select.selectedIndex].value;

            return value;
        }

        function getCategoryChilds()
        {
            table.ajax.reload();
        }

        $('.form-category-delete').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: '¿Estas seguro?',
                text: "Deseas eliminar la categoria del servicio?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor:  '#d33',
                confirmButtonText:  '¡Si, Eliminar!',
                cancelButtonText:   'Cancelar'
            }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
            })
        });

    </script>
@endsection