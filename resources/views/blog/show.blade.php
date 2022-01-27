@extends('layout')

@section('title')
Publicación
@endsection

@section('content')
@include('partials.structure.open-main')
<h1>Publicación</h1>
<hr>
<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="blog-info-tab" data-toggle="pill" href="#blog-info" role="tab" aria-controls="blog-info" aria-selected="true">
            <i class="fas fa-info-circle"></i>&nbsp;Información
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="blog-file-tab" data-toggle="pill" href="#blog-file" role="tab" aria-controls="blog-file" aria-selected="false">
            <i class="far fa-file-alt"></i>&nbsp;Archivos
            <span class="badge badge-light">{{count($blog->files)}}</span>
        </a>
    </li>
</ul>
<div class="tab-content" id="pills-tabContent">
    <div class="tab-pane fade show active" id="blog-info" role="tabpanel" aria-labelledby="blog-info-tab">
        @include('blog.show_detail.info')
    </div>
    <div class="tab-pane fade" id="blog-file" role="tabpanel" aria-labelledby="blog-file-tab">
        @include('blog.show_detail.file')
    </div>
</div>
@include('partials.structure.close-main')
<script>
    $("#blog_form").submit(function(e) {
        e.preventDefault();
        let id = $('#id').val();
        let status = $('#status').val();
        let _token = $("input[name=_token]").val();

        Swal.fire({
            title: '¿Estas seguro?',
            text: "Cambiar el estado del blog?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '¡Si, Cambiar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: "{{route('blog-update')}}",
                    type: "PUT",
                    data: {
                        id: id,
                        status: status,
                        _token: _token
                    },
                    success: function(data) {
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