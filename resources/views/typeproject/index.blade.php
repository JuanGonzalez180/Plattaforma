@extends('layout')

@section('title')
    Tipos de Proyectos
@endsection


@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Tipos de Proyectos</h1>
            </div>
            <div class="col text-right">
                <a type="button" class="btn btn-primary btn-sm" href="{{ route('typeproject.create') }}"><i class="fas fa-plus"></i>&nbsp;Crear Tipo de Proyecto</a>
            </div>
        </div>
        <hr>
        @if(session()->get('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        @endif

        <div class="form-group col-md-6">
            <label for="parent_id">Proyectos</label>
            <select name="parent_id" id="parent_id" class="form-control form-control-sm" onchange="getProjectChilds(this.value);">
            @foreach($parents as $parent)
                <option value="{{$parent->id}}">{{$parent->name}}</option>
            @endforeach
            </select>
        </div>

        <table id="project_table" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Proyecto</th>
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
            table = $('#project_table').DataTable( {
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('type.projects.childs') }}",
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

        function getProjectChilds()
        {
            table.ajax.reload();
        }
    </script>
@endsection