@extends('layout')

@section('title')
    Proyecto
@endsection

@section('content')
    @include('partials.structure.open-main')
    <h1>Proyecto</h1>
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="project-info-tab" data-toggle="pill" href="#project-info" role="tab" aria-controls="project-info" aria-selected="true">
                <i class="fas fa-info-circle"></i>&nbsp;Información
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="project-file-tab" data-toggle="pill" href="#project-file" role="tab" aria-controls="project-file" aria-selected="false">
                <i class="far fa-file-alt"></i>&nbsp;Archivos
                <span class="badge badge-light">{{count($project->files)}}</span>
            </a>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="project-info" role="tabpanel" aria-labelledby="project-info-tab">
            @include('project.show_detail.info')
        </div>
        <div class="tab-pane fade" id="project-file" role="tabpanel" aria-labelledby="project-file-tab">
            @include('project.show_detail.files')
        </div>
    </div>
    @include('partials.structure.close-main')
    <script>
        $("#project_form").submit(function(e){
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
                        url: "{{route('project-companies-update')}}",
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
    </script>
@endsection