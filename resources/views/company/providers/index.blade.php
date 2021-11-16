@extends('layout')

@section('title')
Proveedores
@endsection


@section('content')
@include('partials.structure.open-main')
<div class="row align-items-center">
    <div class="col">
        <h2>Comunidad <span class="badge badge-secondary">Proveedores</span></h2>
    </div>
</div>
<hr>
@if(session()->get('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif


<div class="container">
    <div class="row">
        <div class="col-sm">
            <label for="parent_id">Estado</label>
            <select name="status" id="status" class="form-control form-control-sm" onchange="getCompany(this.value);">
                <option value="all">Todos</option>
                @foreach($status as $value)
                <option value="{{$value}}">{{$value}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm">
            <label for="parent_id">Tamaño</label>
            <select name="size" id="size" class="form-control form-control-sm" onchange="getCompany();">
                <option value="desc">Mayor</option>
                <option value="asc">Menor</option>
            </select>
        </div>
        <!-- <div class="col-sm">
            <table class="table table-bordered" style="text-align: center">
                <thead>
                    <tr>
                        <th scope="col">Total</th>
                        <th scope="col">Creado</th>
                        <th scope="col">Aprobado</th>
                        <th scope="col">Rechazado</th>
                        <th scope="col">Bloqueado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($statusArrayCount as $value)
                        <td>{{$value}}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div> -->
    </div>
</div>
<br>

@include('partials.session-status')
<table id="company_table" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Entidad</th>
            <th>Fecha</th>
            <th>Espacio</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
</table>

@include('partials.structure.close-main')
<script>
    var table;

    $(document).ready(function() {
        table = $('#company_table').DataTable({
            "serverSide": true,
            "ordering": false,
            "ajax": {
                "url": "{{ route('companies-get-providers') }}",
                "type": "POST",
                "headers": {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                "data": function(d) {
                    d.status    = getStatus();
                    d.size      = getSize();
                }
            },
            "columns": [{
                    data: 'id'
                },
                {
                    data: 'name'
                },
                {
                    data: 'entity'
                },
                {
                    data: 'date'
                },
                {
                    data: 'size_company'
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

    function getStatus() {
        var select = document.getElementById('status');
        var value = select.options[select.selectedIndex].value;

        return value;
    }

    function getSize() {
        var select = document.getElementById('size');
        var value = select.options[select.selectedIndex].value;

        return value;
    }

    function getCompany() {
        table.ajax.reload();
    }

    function editStatusCreated(id) {

        Swal.fire({
            title: 'Desea cambiar el estado de la compañia?',
            icon: 'warning',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Aprobar',
            denyButtonText: `Rechazar`,
            cancelButtonText: `Cancelar`,
        }).then((result) => {

            if (result.isConfirmed) {

                editStatusUpdate(id, 'Aprobado');

            } else if (result.isDenied) {

                editStatusUpdate(id, 'Rechazado');
            }
        })
    }

    function editStatusRejected(id) {

        Swal.fire({
            title: 'Desea cambiar el estado de la compañia?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Aprobar',

            cancelButtonText: `Cancelar`,
        }).then((result) => {

            if (result.isConfirmed) {

                editStatusUpdate(id, 'Aprobado');

            } else if (result.isDenied) {

                editStatusUpdate(id, 'Rechazado');
            }
        })
    }

    function editStatusUpdate(id, status) {
        let _token = $("input[name=_token]").val();

        $.ajax({
            url: "{{route('company.edit.status')}}",
            type: "POST",
            data: {
                id: id,
                status: status,
                _token: _token
            },
            success: function(data) {
                Swal.fire(
                    'Estado editado !!',
                    data.message,
                    'success'
                )
                table.ajax.reload();
            }
        });
    }
</script>
@endsection