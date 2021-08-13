@extends('layout')

@section('title')
    Compañia
@endsection

@section('content')
    @include('partials.structure.open-main')
    
        <h1>Compañia</h1>

        <dl class="row">
            <dt class="col-sm-4">Nombre:</dt>
            <dd class="col-sm-8">{{$company->name}}</dd>

            <dt class="col-sm-4">Tipo de entidad:</dt>
            <dd class="col-sm-8">{{$company->type_entity->name}} <b>({{$company->type_entity->type->name}})</b></dd>

            <dt class="col-sm-4">NIT:</dt>
            <dd class="col-sm-8"><b>{{$company->nit}}</b></dd>

            <dt class="col-sm-4">Descripción:</dt>
            <dd class="col-sm-8">
                @if(is_null($company->description))
                    <span class="badge badge-secondary">Sin descripción</span>
                @else
                    {{$company->description}}
                @endif
            </dd>
            
            <dt class="col-sm-4">Estado:</dt>
            <dd class="col-sm-8">
                @if($company->status == 'Creado')
                    <span class="badge badge-warning">{{$company->nit}}</span>
                @elseif($company->status == 'Aprobado')
                    <span class="badge badge-success">{{$company->status}}</span>
                @else
                    <span class="badge badge-danger">{{$company->status}}</span>
                @endif
            </dd>

            <dt class="col-sm-4">Administrador:</dt>
            <dd class="col-sm-8">{{$company->user->username}}</dd>

            <dt class="col-sm-4">Correo</dt>
            <dd class="col-sm-8">{{$company->user->email}}</dd>

            <dt class="col-sm-4">Pais de la compañia:</dt>
            <dd class="col-sm-8">{{$company->country_code}}</dd>

            <dt class="col-sm-4">Dirección:</dt>
            <dd class="col-sm-8">
                @if(!is_null($company->address) && !is_null($company->address->address))
                    {{ $company->address->address }}
                @else
                    <span class="badge badge-secondary">Sin dirección</span>
                @endif
            </dd>

            <dt class="col-sm-4">Pagina web:</dt>
            <dd class="col-sm-8">
                @if(is_null($company->web))
                    <span class="badge badge-secondary">Sin pagina web</span>
                @else
                    {{$company->web}}
                @endif
            </dd>

            <dt class="col-sm-4">País principal en el que va a operar:</dt>
            <dd class="col-sm-8">
                @foreach($company->countries as $country)
                    {{$country->name}}
                @endforeach
            </dd>
        </dl>
    @include('partials.structure.close-main')
@endsection