@extends('layout')

@section('title')
    Producto
@endsection

@section('content')
    @include('partials.structure.open-main')
    <h1>Producto</h1>
    <hr>
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="product-info-tab" data-toggle="pill" href="#product-info" role="tab" aria-controls="product-info" aria-selected="true">
                <i class="fas fa-info-circle"></i>&nbsp;Información
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="product-file-tab" data-toggle="pill" href="#product-file" role="tab" aria-controls="product-file" aria-selected="false">
                <i class="far fa-file-alt"></i>&nbsp;Archivos
                <span class="badge badge-light">{{count($product->files)}}</span>
            </a>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="product-info" role="tabpanel" aria-labelledby="product-info-tab">
            @include('product.show_detail.info')
        </div>
        <div class="tab-pane fade" id="product-file" role="tabpanel" aria-labelledby="project-file-tab">
            @include('product.show_detail.files')
        </div>
    </div>
    @include('partials.structure.close-main')
@endsection