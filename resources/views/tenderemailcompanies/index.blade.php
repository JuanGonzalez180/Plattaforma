@extends('layout')

@section('title')
Licitaciones
@endsection

@section('content')
@include('partials.structure.open-main')
<div class="row align-items-center">
    <div class="col">
        <h1>Correos de invitación</h1>
    </div>
</div>
<hr>
<div class="container">
    <div class="row">
        <div class="col-sm">
            <label for="company">Licitaciones</label>
            <select name="tender" id="tender" class="form-control form-control-sm" onchange="getCompany();">
                <option value="all">Todas</option>
                @foreach($tenders as $value)
                <option value="{{$value['id']}}">{{ucfirst($value['name'])}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm">
            <label for="parent_id">Ordenar por</label>
            <select name="size" id="size" class="form-control form-control-sm" onchange="getCompany();">
                @foreach($order as $key => $value)
                <option value="{{$key}}">{{$value}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<br>
@include('partials.session-status')
<table id="tender_table" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Correo</th>
            <th scope="col">Licitación</th>
            <th scope="col">Compañia</th>
            <th scope="col">Registrado</th>
            <th scope="col">Fecha</th>
        </tr>
    </thead>
</table>
@include('partials.structure.close-main')
<script>
    var table;

    $(document).ready(function() {
        table = $('#tender_table').DataTable({
            "serverSide": true,
            "ordering": false,
            "ajax": {
                "url": "{{ route('tenders.invitation.email.all') }}",
                "type": "POST",
                "headers": {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                "data": function(d) {
                    d.size = getSize();
                    d.tender = getTender();
                }
            },
            "columns": [{
                    data: 'id'
                },
                {
                    data: 'email'
                },
                {
                    data: 'tender_name'
                },
                {
                    data: 'company_name'
                },
                {
                    data: 'register_email'
                },
                {
                    data: 'date'
                }
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

    function getSize() {
        var select = document.getElementById('size');
        var value = select.options[select.selectedIndex].value;

        return value;
    }

    function getTender() {
        var select = document.getElementById('tender');
        var value = select.options[select.selectedIndex].value;

        return value;
    }

    function getCompany() {
        table.ajax.reload();
    }
</script>
@endsection