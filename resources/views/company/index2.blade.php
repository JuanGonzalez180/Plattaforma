@extends('layout')

@section('title')
    Categorías del servicio
@endsection


@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Compañias</h1>
            </div>
        </div>
        <hr>
        <div class="form-group col-md-6">
            <label for="type_id">Tipo</label>
            <select name="type_id" id="type_id" class="form-control form-control-sm" onchange="getCategoryChilds(this.value);">
            @foreach($types as $type)
                <option value="{{$type->id}}">{{$type->name}}</option>
            @endforeach
            </select>
        </div>

        <table id="company_table" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Entidad</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
        </table>
    @include('partials.structure.close-main')
    <script>
        $('#form-company-approve').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: '¿Estas seguro?',
                text: "La compañia estara aprobada",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor:  '#d33',
                confirmButtonText:  '¡Si, Aprobar!',
                cancelButtonText:   'Cancelar'
            }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
            })
        });
        $('.form-company-disapprove').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: '¿Estas seguro?',
                text: "La compañia estara desaprobada",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor:  '#d33',
                confirmButtonText:  '¡Si, Rechazar!',
                cancelButtonText:   'Cancelar'
            }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
            })
        });

        $(document).ready(function(){      
            table = $('#company_table').DataTable( {
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('companies.type') }}",
                    "type": "POST",
                    "headers": {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    "data": function(d){
                        d.type_id = getTypeCompany();
                    }
                },
                "columns" : [
                    {data: 'id'},
                    {data: 'name'},
                    {data: 'type_entity'},
                    {data: 'status'},
                    {data: 'action'},
                ],
                "order": [[ 4, "desc" ]],
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

        function getTypeCompany()
        {
            var select  = document.getElementById('type_id');
            var value   = select.options[select.selectedIndex].value;

            return value;
        }

        function getCategoryChilds()
        {
            table.ajax.reload();
        }

        function prueba()
        {
            return 'hola';
        }

        

    </script>

@endsection