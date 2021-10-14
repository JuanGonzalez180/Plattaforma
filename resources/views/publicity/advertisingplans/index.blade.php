@extends('layout')

@section('title')
Planes Publicidad
@endsection

@section('content')
@include('partials.structure.open-main')

<div class="row align-items-center">
    <div class="col">
        <h2>Planes publicidad</h2>
    </div>
    <div class="col text-right">
        <a type="button" class="btn btn-primary" href="{{ route('publicity_plan.create') }}"><span class="oi oi-plus" title="Nuevo" aria-hidden="true"></span> Crear Plan</a>
    </div>
</div>
@if(session()->get('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif
<br>
@include('partials.session-status')

<div class="card">
    <div class="card-header">Planes</div>
    <div class="card-body">
        <ul class="list-group">
            @foreach($plans as $plan)
            <li class="list-group-item clearfix">
                <div class="pull-left">
                    <h5>{{ $plan->name }}</h5>
                    <div><b>Descripción:</b> {{ $plan->description }}</div>
                    <div><b>Precio:</b> ${{ number_format($plan->price, 2) }}</div>
                    <div><b>tipo:</b> {{ $plan->type_ubication }}</div>
                    <div><b>Días de prueba:</b> {{ $plan->days }}</div><br>

                    <a type="button" href="{{ route('publicity_plan.edit', $plan->id ) }}" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> Editar </a>
                    <form method="POST" action="{{ route('publicity_plan.destroy', $plan->id ) }}" class="d-inline">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseas Eliminar el plan de publicidad?')" data-toggle="tooltip" title='Eliminar'> <i class="oi oi-trash"> </i> Eliminar </button>
                    </form>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
</div>



@include('partials.structure.close-main')

@endsection