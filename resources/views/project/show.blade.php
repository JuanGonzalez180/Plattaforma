@extends('layout')

@section('title')
    Proyecto
@endsection

@section('content')
    @include('partials.structure.open-main')

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Compañias</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/project/company/'.$project->company_id) }}">Proyectos</a></li>
                <li class="breadcrumb-item active">Detalle del proyecto</li>
            </ol>
        </nav>
        <h1>Proyecto</h1>

        <dl class="row">
            <dt class="col-sm-4">Nombre:</dt>
            <dd class="col-sm-8">{{ $project->name }}</dd>
            <dt class="col-sm-4">Encargado:</dt>
            <dd class="col-sm-8">{{ $project->user->username }}</dd>
            <dt class="col-sm-4">Compañia:</dt>
            <dd class="col-sm-8">{{ $project->company->name }}</dd>
            <dt class="col-sm-4">Descripción:</dt>
            <dd class="col-sm-8">{{ $project->description }}</dd>
            <dt class="col-sm-4">Dirección:</dt>
            <dd class="col-sm-8">
                @if(!is_null($project->address) && !is_null($project->address->address))
                    {{ $project->address->address }}
                @else
                    <span class="badge badge-secondary">Sin dirección</span>
                @endif
            </dd>
            <dt class="col-sm-4">Metros cuadrados:</dt>
            <dd class="col-sm-8">{{ $project->meters }}</dd>
            <dt class="col-sm-4">Fecha de inicio del proyecto:</dt>
            <dd class="col-sm-8">{{ $project->date_start }}</dd>
            <dt class="col-sm-4">Fecha final del proyecto:</dt>
            <dd class="col-sm-8">{{ $project->date_end }}</dd>


            <dt class="col-sm-4">Estado:</dt>
            <dd class="col-sm-8">
                @if($project->status == 'especificaciones-tecnicas')
                    <span class="badge badge-warning">{{ $project->status }}</span>
                @else
                    <span class="badge badge-danger">{{ $project->status }}</span>
                @endif
            </dd>



            <dt class="col-sm-4">Visible:</dt>
            <dd class="col-sm-8">{{ $project->visible }}</dd>
        </dl>

    @include('partials.structure.close-main')
@endsection