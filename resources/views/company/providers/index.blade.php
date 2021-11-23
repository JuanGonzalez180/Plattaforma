@extends('layout')

@section('title')
Proveedores
@endsection


@section('content')
@include('partials.structure.open-main')
<div class="row align-items-center">
    <div class="col">
        <h4>Comunidad <span class="badge badge-secondary">Proveedores</span></h4>
    </div>
    <div class="col">
        <table class="table table-sm table-bordered" style="text-align: center" id=table_status>
        </table>
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
            <select name="status" id="status" class="form-control form-control-sm" onchange="getCompany();">
                <option value="all">Todos</option>
                @foreach($status as $key => $value)
                <option value="{{$key}}">{{$value}}</option>
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
    var table_status = document.getElementById('table_status');

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
                    d.status = getStatus();
                    d.size = getSize();
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
        getStatusCount();
    });

    function getStatus() {
        var select = document.getElementById('status');
        var value = select.options[select.selectedIndex].value;

        return value;
    }

    function getStatusCount() {
        let _token = $("input[name=_token]").val();

        $.ajax({
            url: "{{route('companies-status-providers')}}",
            type: "POST",
            data: {
                _token: _token
            },
            success: function(data) {
                console.log(data);

                let HTMLString = `
                <thead>
                    <tr>
                        <th scope="col">Nueva</th>
                        <th scope="col">Aprobado</th>
                        <th scope="col">Rechazado</th>
                        <th scope="col">Bloqueado</th>
                        <th scope="col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            ` + data[0] + `
                        </td>
                        <td>
                            ` + data[1] + `
                        </td>
                        <td>
                            ` + data[2] + `
                        </td>
                        <td>
                            ` + data[3] + `
                        </td>
                        <td>
                            ` + data[4] + `
                        </td>
                    </tr>
                </tbody>
                `;

                table_status.innerHTML = HTMLString;
            }
        });
    }

    function getSize() {
        var select = document.getElementById('size');
        var value = select.options[select.selectedIndex].value;

        return value;
    }

    function getCompany() {
        table.ajax.reload();
        getStatusCount();
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

    function editStatusLock(id) {
        Swal.fire({
            title: 'Desea bloquear la compañia?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Bloquear',

            cancelButtonText: `Cancelar`,
        }).then((result) => {
            if (result.isConfirmed) {
                editStatusUpdate(id, 'Bloqueado');
            }
        })
    }

    function editStatusUnlock(id) {
        Swal.fire({
            title: 'Desea desbloquear la compañia?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Desbloquear',

            cancelButtonText: `Cancelar`,
        }).then((result) => {
            if (result.isConfirmed) {
                editStatusUpdate(id, 'Aprobado');
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
                getStatusCount();
            }
        });
    }
</script>
@endsection