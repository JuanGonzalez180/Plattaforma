@extends('layout')

@section('title')
Gestionar publicidad
@endsection

@section('content')
@include('partials.structure.open-main')

<div class="row align-items-center">
    <div class="col">
        <h2>Gestionar publicidad</h2>
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
            <label for="parent_id">Compañias</label>
            <select name="company_id" id="company_id" class="form-control form-control-sm" onchange="getPlanCompany(this.value);">
                <option value="all">Todos</option>
                @foreach($companies as $company)
                <option value="{{$company->id}}">{{$company->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm">
            <label for="parent_id">Estado</label>
            <select name="status" id="status" class="form-control form-control-sm" onchange="getPlanCompany(this.value);">
                <option value="all">Todos</option>
                @foreach($status as $key => $value)
                <option value="{{$key}}">{{$value}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<br>

@include('partials.session-status')

<table id="manage_advertising_table" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Publicidad</th>
            <th scope="col">Plan</th>
            <th scope="col">Estado</th>
            <th scope="col">Acciones</th>
        </tr>
    </thead>
</table>

@include('partials.structure.close-main')
<script>
    var table;
    $(document).ready(function() {
        table = $('#manage_advertising_table').DataTable({
            "serverSide": true,
            "ajax": {
                "url": "{{ route('manage_publicity_plan.company') }}",
                "type": "POST",
                "headers": {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                "data": function(d) {
                    d.company_id = getCompanyId();
                    d.status = getStatus();
                }
            },
            "columns": [{
                    data: 'id'
                },
                {
                    data: 'name'
                },
                {
                    data: 'plan'
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

    function getCompanyId() {
        var select  = document.getElementById('company_id');
        var value   = select.options[select.selectedIndex].value;

        return value;
    }

    function getStatus() {
        var select  = document.getElementById('status');
        var value   = select.options[select.selectedIndex].value;

        return value;
    }

    function getPlanCompany() {
        table.ajax.reload();
    }
</script>

@endsection