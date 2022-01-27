@extends('layout')

@section('title')
Tipos de Entidad
@endsection

@section('content')
@include('partials.structure.open-main')
<div class="row align-items-center">
    <div class="col">
        <h1>Tipos de comunidad</h1>
    </div>
    <div class="col text-right">
        <a type="button" class="btn btn-primary btn-sm" href="{{ route('typesentity.create') }}"><i class="fas fa-plus"></i>&nbsp;Crear Tipo de comunidad</a>
    </div>
</div>
<hr>
@if(session()->get('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif
<div class="form-group col-md-6">
    <label for="parent_id">Tipo</label>
    <select name="type_id" id="type_id" class="form-control form-control-sm" onchange="getTypeEntity(this.value);">
        <option value="all">Todos</option>
        @foreach($types as $type)
        <option value="{{$type->id}}">{{$type->renameType()}}</option>
        @endforeach
    </select>
</div>
@include('partials.session-status')
<table id="type_entity_table" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
</table>
@include('partials.structure.close-main')
<script>
    var table;
    $(document).ready(function() {
        table = $('#type_entity_table').DataTable({
            "serverSide": true,
            "ajax": {
                "url": "{{ route('typesentity.type') }}",
                "type": "POST",
                "headers": {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                "data": function(d) {
                    d.type_id = getTypeId();
                }
            },
            "columns": [{
                    data: 'id'
                },
                {
                    data: 'name'
                },
                {
                    data: 'type'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action'
                },
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
                "paginate": {
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });
    });

    function getTypeId() {
        var select  = document.getElementById('type_id');
        var value   = select.options[select.selectedIndex].value;

        return value;
    }

    function getTypeEntity() {
        table.ajax.reload();
    }
</script>
@endsection