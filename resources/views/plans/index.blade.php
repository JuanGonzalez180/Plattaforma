@extends('layout')

@section('title')
    Planes Stripe
@endsection


@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Planes Stripe</h1>
            </div>
            <div class="col text-right">
                <a type="button" class="btn btn-primary" href="{{ route('plans.create') }}"><span class="oi oi-plus" title="Nuevo" aria-hidden="true"></span> Crear Plan</a>
            </div>
        </div>
        @if(session()->get('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">Planes</div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($plans as $plan)
                    <li class="list-group-item clearfix">
                        <div class="pull-left">
                            <h5>{{ $plan->name }}</h5>
                            <div>${{ number_format($plan->cost, 2) }} <b>{{$plan->iso}}</b> cada {{ $plan->interval_count }} {{ $plan->interval }} </div>
                            <div><b>Descripción:</b> {{ $plan->description }}</div>
                            <div>Días de prueba: <b>{{ $plan->days_trials }}</b></div><br>
                            
                            <a type="button" href="{{ route('plans.edit', $plan->id ) }}" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> Editar </a>
                            <form method="POST" action="{{ route('plans.destroy', $plan->id ) }}" class="d-inline">
                                @method('DELETE')
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseas Eliminar el plan de stripe?')" data-toggle="tooltip" title='Eliminar'> <i class="oi oi-trash"> </i> Eliminar </button>
                            </form>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @include('partials.structure.close-main')
@endsection