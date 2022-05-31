@extends('layout')

@section('title')
Licitaciones
@endsection

@section('content')
@include('partials.structure.open-main')
<div class="row align-items-center">
    <div class="col">
        <h1>Licitaciones</h1>
    </div>
</div>
<hr>
<div class="container">
    <div class="row">
        <div class="col-sm">
            <label for="company">Compañia</label>
            <select name="company" id="company" class="form-control form-control-sm" onchange="reloadTable();">
                <option value="all">Todas</option>
                @foreach($companies as $key => $value)
                <option value="{{$value['id']}}">{{strtoupper($value['name'])}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm">
            <label for="status">Estado</label>
            <select name="status" id="status" class="form-control form-control-sm" onchange="reloadTable();">
                <option value="all">Todas</option>
                @foreach($tenderStatus as $key => $value)
                <option value="{{$key}}">{{$value}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm">
            <label for="order">Ordernar por</label>
            <select name="order" id="order" class="form-control form-control-sm" onchange="reloadTable();">
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
            <th scope="col">Nombre</th>
            <th scope="col">Tipo</th>
            <th scope="col">Estado</th>
            <th scope="col">Responsable</th>
            <th scope="col">Compañia</th>
            <th scope="col">Acciones</th>
        </tr>
    </thead>
</table>
@include('partials.structure.close-main')
<script>
    var table;

    $(document).ready(function()
    {      
            table = $('#tender_table').DataTable( {
                "serverSide": true,
                "ordering": false,
                "ajax": {
                    "url": "{{ route('tenders.companies.all') }}",
                    "type": "POST",
                    "headers": {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    "data": function(d){
                        d.company   = getCompany();
                        d.status    = getStatus();
                        d.orders    = getOrder();
                    }
                },
                "columns" : [
                    {data: 'id'},
                    {data: 'name'},
                    {data: 'type'},
                    {data: 'version_status'},
                    {data: 'user_id'},
                    {data: 'company_id'},
                    {data: 'action'},
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

    function reloadTable() {
        table.ajax.reload();
    }

    function getCompany() {
        var select = document.getElementById('company');
        var value = select.options[select.selectedIndex].value;
        return value;
    }

    function getStatus() {
        var select = document.getElementById('status');
        var value = select.options[select.selectedIndex].value;
        return value;
    }

    function getOrder() {
        var select = document.getElementById('order');
        var value = select.options[select.selectedIndex].value;
        return value;
    }

</script>

@endsection