@extends('layout')

@section('title')
    Portafolio
@endsection

@section('content')
    @include('partials.structure.open-main')
    <h1>Portafolio</h1>
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
                <span class="badge badge-light">{{count($portfolio->files)}}</span>
            </a>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="blog-info" role="tabpanel" aria-labelledby="blog-info-tab">
            @include('portfolio.show_detail.info')
        </div>
        <div class="tab-pane fade" id="blog-file" role="tabpanel" aria-labelledby="blog-file-tab">
            @include('portfolio.show_detail.file')
        </div>
    </div>
    @include('partials.structure.close-main')
@endsection