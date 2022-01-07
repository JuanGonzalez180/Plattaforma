@extends('layout')

@section('title')
Publicidad
@endsection

@section('content')
@include('partials.structure.open-main')
<h1>Publicidad</h1>
<hr>
@if(session()->get('success'))
<div class="alert alert-success">
    <i class="fas fa-check"></i>&nbsp;{{ session()->get('success') }}
</div>
@endif

@if(session()->get('danger'))
<div class="alert alert-danger">
    <i class="fas fa-times"></i>&nbsp;{{ session()->get('danger') }}
</div>
@endif

<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="advertising-info-tab" data-toggle="pill" href="#advertising-info" role="tab" aria-controls="advertising-info" aria-selected="true">
            <i class="fas fa-info-circle"></i>&nbsp;Información
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="advertising-plan-tab" data-toggle="pill" href="#advertising-plan" role="tab" aria-controls="advertising-plan" aria-selected="false">
            <i class="fas fa-book-open"></i>&nbsp;Plan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="advertising-payment-tab" data-toggle="pill" href="#advertising-payment" role="tab" aria-controls="advertising-payment" aria-selected="false">
            <i class="fas fa-money-bill-alt"></i>&nbsp;Pago
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="advertising-payment-tab" data-toggle="pill" href="#advertising-plan-img" role="tab" aria-controls="advertising-plan-img" aria-selected="false">
            <i class="fas fa-images"></i>&nbsp;Plan imagenes
        </a>
    </li>
</ul>
<div class="tab-content" id="pills-tabContent">
    <div class="tab-pane fade show active" id="advertising-info" role="tabpanel" aria-labelledby="advertising-info-tab">
        @include('publicity.manageadvertising.show_detail.info')
    </div>
    <div class="tab-pane fade" id="advertising-plan" role="tabpanel" aria-labelledby="project-file-tab">
        @include('publicity.manageadvertising.show_detail.plan')
    </div>
    <div class="tab-pane fade" id="advertising-payment" role="tabpanel" aria-labelledby="project-file-tab">
        @include('publicity.manageadvertising.show_detail.payment')
    </div>
    <div class="tab-pane fade" id="advertising-plan-img" role="tabpanel" aria-labelledby="project-file-tab">
        @include('publicity.manageadvertising.show_detail.images')
    </div>
</div>
@include('partials.structure.close-main')
<script>
    $('.form-advertising-status').submit(function(e) {
        console.log('pasa por aca');
        e.preventDefault();
        Swal.fire({
            title: '¿Estas seguro?',
            text: "Deseas cambiar el estado de la publicidad?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '¡Si, Cambiar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        })
    });
</script>
@endsection