@extends('layout')

@section('title')
    Licitación
@endsection

@section('content')
    @include('partials.structure.open-main')

    <h1>Licitación</h1>

    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        
        <li class="nav-item">
            <a class="nav-link active" id="tender-info-tab" data-toggle="pill" href="#tender-info" role="tab" aria-controls="tender-info" aria-selected="true">
                <i class="fas fa-info-circle"></i>&nbsp;Información
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tender-company-tab" data-toggle="pill" href="#tender-company" role="tab" aria-controls="tender-company" aria-selected="false">
            <i class="far fa-building"></i>&nbsp;Compañias licitantes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tender-versions-tab" data-toggle="pill" href="#tender-versions" role="tab" aria-controls="tender-versions" aria-selected="false">
            <i class="fas fa-code-branch"></i>&nbsp;Versiones
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tender-question-tab" data-toggle="pill" href="#tender-question" role="tab" aria-controls="tender-question" aria-selected="false">
            <i class="fas fa-code-branch"></i>&nbsp;Muro de consultas
            </a>
        </li>
    
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="tender-info" role="tabpanel" aria-labelledby="tender-info-tab">
            @include('tender.show_detail.info')
        </div>

        <div class="tab-pane fade" id="tender-company" role="tabpanel" aria-labelledby="tender-company-tab">
            @include('tender.show_detail.tenderCompanies')
        </div>

        <div class="tab-pane fade" id="tender-versions" role="tabpanel" aria-labelledby="tender-versions-tab">
            @include('tender.show_detail.tenderVersions')
        </div>

        <div class="tab-pane fade" id="tender-question" role="tabpanel" aria-labelledby="tender-question-tab">
            @include('tender.show_detail.tenderWall')
        </div>

    </div>
    @include('partials.structure.close-main')
@endsection