@extends('layout')

@section('title')
Cotización
@endsection

@section('content')
@include('partials.structure.open-main')
<h1>Cotización</h1>
<hr>
<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="quote-info-tab" data-toggle="pill" href="#quote-info" role="tab" aria-controls="quote-info" aria-selected="true">
            <i class="fas fa-info-circle"></i>&nbsp;Información
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="quote-version-tab" data-toggle="pill" href="#quote-version" role="tab" aria-controls="quote-version" aria-selected="false">
            <i class="fas fa-code-branch"></i>&nbsp;Versiones
            <span class="badge badge-light">{{count($quote->quotesVersion)}}</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('quote-companies-id', $quote->id ) }}" target=”_blank” aria-controls="tender-company">
            <i class="far fa-building"></i>&nbsp;Compañias cotizantes
        </a>
    </li>
</ul>
<div class="tab-content" id="pills-tabContent">
    <div class="tab-pane fade show active" id="quote-info" role="tabpanel" aria-labelledby="quote-info-tab">
        @include('quote.general.show_detail.info')
    </div>
    
    <div class="tab-pane fade" id="quote-version" role="tabpanel" aria-labelledby="quote-version-tab">
        @include('quote.general.show_detail.quoteVersions')
    </div>
</div>


@include('partials.structure.close-main')
@endsection