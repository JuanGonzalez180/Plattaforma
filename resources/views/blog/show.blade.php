@extends('layout')

@section('title')
    Blog
@endsection

@section('content')
    @include('partials.structure.open-main')
    <h1>Blog</h1>
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="blog-info-tab" data-toggle="pill" href="#blog-info" role="tab" aria-controls="blog-info" aria-selected="true">
                <i class="fas fa-info-circle"></i>&nbsp;Información
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="blof-file-tab" data-toggle="pill" href="#blof-file" role="tab" aria-controls="blof-file" aria-selected="false">
                <i class="far fa-file-alt"></i>&nbsp;Archivos
            </a>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="blog-info" role="tabpanel" aria-labelledby="blog-info-tab">
            @include('blog.show_detail.info')
        </div>
        <div class="tab-pane fade" id="blof-file" role="tabpanel" aria-labelledby="blof-file-tab">
        @include('blog.show_detail.file')
        </div>
    </div>

    @include('partials.structure.close-main')
@endsection