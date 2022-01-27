@extends('layout')

@section('title')
    Usuario
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Usuario</h1>
        <br><br>
        <dl class="row">
            <dt class="col-sm-4">Id:</dt>
            <dd class="col-sm-8">{{ $user->id }}</dd>

            <dt class="col-sm-4">Correo electrónico:</dt>
            <dd class="col-sm-8">{{ $user->email }}</dd>

            <dt class="col-sm-4">Usuario:</dt>
            <dd class="col-sm-8">{{ $user->username }}</dd>

            <dt class="col-sm-4">Compañía:</dt>
            <dd class="col-sm-8">{{ ( count($user->company) ) ? $user->company[0]['name'] : '' }}</dd>

            <dt class="col-sm-4">Nit:</dt>
            <dd class="col-sm-8">{{ ( count($user->company) ) ? $user->company[0]['nit'] : '' }}</dd>

            <dt class="col-sm-4">País de la compañía:</dt>
            <dd class="col-sm-8">{{ ( count($user->company) ) ? $user->company[0]['country_code'] : '' }}</dd>

            <dt class="col-sm-4">Página Web:</dt>
            <dd class="col-sm-8">{!! ( count($user->company) ) ? '<a href="'.$user->company[0]['web'].'" target="_blank">' . $user->company[0]['web'] . '</a>' : '' !!}</dd>

            <dt class="col-sm-4">Entidad:</dt>
            <dd class="col-sm-8">{{ ( count($user->company) ) ? $user->company[0]['type_entity']['name'] : '' }}</dd>

            <dt class="col-sm-4">País principal en el que va a operar:</dt>
            <dd class="col-sm-8">{{ ( count($user->company) ) ? (( count($user->company[0]->countries) ) ? $user->company[0]->countries[0]['name'] : '') : '' }}</dd>
        </dl>
        
        <div class="row">
            <div class="col-sm-12">
                <!--href="{{ route('users.index') }}"--> 
                <a type="button" class="btn btn-danger"><span class="oi oi-x" title="Atrás" aria-hidden="true"></span> Atrás</a>
            </div>
        </div>
                
    @include('partials.structure.close-main')
@endsection