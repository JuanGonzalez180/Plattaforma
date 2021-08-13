@extends('layout')

@section('title')
    Licitación
@endsection

@section('content')
    @include('partials.structure.open-main')

        <h1>Licitación</h1>

        <dl class="row">
            <dt class="col-sm-4">Nombre:</dt>
            <dd class="col-sm-8">{{$tender->name}}</dd>
            <dt class="col-sm-4">Descripción:</dt>
            <dd class="col-sm-8">
                @if(is_null($tender->description))
                    <span class="badge badge-secondary">Sin descripción</span>
                @else
                    {{$tender->description}}
                @endif
            </dd>
            <dt class="col-sm-4">Proyecto:</dt>
            <dd class="col-sm-8">{{$tender->project->name}}</dd>
            <dt class="col-sm-4">Compañia:</dt>
            <dd class="col-sm-8">{{$tender->company->name}}</dd>
            <dt class="col-sm-4">Usuario encargado:</dt>
            <dd class="col-sm-8">{{$tender->user->username}}</dd>
            <dd class="col-sm-12">


            @if(count($tender->tendersVersion)>0)
                <dt class="col-sm-12">Versiones de la licitación:</dt>
                <dd class="col-sm-12">
                    <div class="accordion" id="accordionExample">
                        @foreach($tender->tendersVersion->sortBy([ ['updated_at', 'desc'] ]) as $key=>$version)
                        <div class="card">
                            <div class="card-header" id="heading{{$key}}">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left {{ ($key > 0)? 'collapsed' : ''}}" type="button" data-toggle="collapse" data-target="#collapse{{$key}}" aria-expanded="{{ ($key > 0)? 'true' : 'false'}}" aria-controls="collapse{{$key}}">
                                {{$version->updated_at}}
                                </button>
                            </h2>
                            </div>
                            <div id="collapse{{$key}}" class="collapse {{ ($key > 0)? '' : 'show'}}" aria-labelledby="heading{{$key}}" data-parent="#accordionExample">
                                <div class="card-body">
                                    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </dd>
            @endif
        </dl>
    @include('partials.structure.close-main')
@endsection