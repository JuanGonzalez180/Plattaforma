@extends('layout')

@section('title')
    Compañia cotizante
@endsection

@section('content')
    @include('partials.structure.open-main')
    <h1>Compañia cotizante</h1>
    <hr>
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="tender-companies-tab" data-toggle="pill" href="#tender-companies" role="tab" aria-controls="tender-companies" aria-selected="true">
                <i class="fas fa-info-circle"></i>&nbsp;Información
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="file-company-tab" data-toggle="pill" href="#file-company" role="tab" aria-controls="file-company" aria-selected="false">
                <i class="far fa-file-alt"></i>&nbsp;Archivos
            </a>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="tender-companies" role="tabpanel" aria-labelledby="tender-companies-tab">
            @include('quotecompanies.show_detail.info')
        </div>
        <div class="tab-pane fade" id="file-company" role="tabpanel" aria-labelledby="file-company-tab">
            @include('quotecompanies.show_detail.files')
        </div>
    </div>

    @include('partials.structure.close-main')
    <!-- <script>
        $("#tender_company_forms").submit(function(e){
            e.preventDefault();
            let id      = $('#id').val();
            let status  = $('#status').val();
            let _token  = $("input[name=_token]").val();

            Swal.fire({
                title: '¿Estas seguro?',
                text: "Cambiar el estado de la compañia licitante ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor:  '#d33',
                confirmButtonText:  '¡Si, Cambiar!',
                cancelButtonText:   'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{route('tender-companies-update')}}",
                        type:"PUT",
                        data:{
                            id: id,
                            status: status,
                            _token: _token
                        },
                        success:function(data)
                        {
                            Swal.fire(
                                'Exito!',
                                data['message'],
                                'success',
                            ).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            })
                        }
                    });
                }
            })
        });
    </script> -->
@endsection