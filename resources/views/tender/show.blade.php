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
            <dt class="col-sm-12">Versiones de la licitación:</dt>
            <dd class="col-sm-12">

            <div id="accordion" style="width: 100%;">
            @forelse($tender->tendersVersion as $tenderversion)
                        <div class="card">
                            <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Collapsible Group Item #1
                                </button>
                            </h5>
                            </div>
    
                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                            </div>
                            </div>
                        </div>

                        
                        <div class="card">
                            <div class="card-header" id="headingThree">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Collapsible Group Item #3
                                </button>
                            </h5>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                            <div class="card-body">
                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                            </div>
                            </div>
                        </div>
                @endforelse    
                </div>
            </dd>




        </dl>
    @include('partials.structure.close-main')
@endsection