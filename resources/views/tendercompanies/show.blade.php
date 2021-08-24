@extends('layout')

@section('title')
    Compañia licitante
@endsection

@section('content')
    @include('partials.structure.open-main')
    <h1>Compañia licitante</h1>
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
            @include('tendercompanies.show_detail.info')
        </div>
        <div class="tab-pane fade" id="file-company" role="tabpanel" aria-labelledby="file-company-tab">
            @include('tendercompanies.show_detail.files')
        </div>
    </div>

    @include('partials.structure.close-main')
@endsection