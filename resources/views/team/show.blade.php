@extends('layout')

@section('title')
    Usuario
@endsection

@section('content')
    @include('partials.structure.open-main')
    <h1>Usuario</h1>
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="team-info-tab" data-toggle="pill" href="#team-info" role="tab" aria-controls="team-info" aria-selected="true">
                <i class="fas fa-info-circle"></i>&nbsp;Información
            </a>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="team-info" role="tabpanel" aria-labelledby="team-info-tab">
            @include('team.show_detail.info')
        </div>
    </div>
    @include('partials.structure.close-main')
@endsection