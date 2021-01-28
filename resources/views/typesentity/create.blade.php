@extends('layout')

@section('title')
    Crear Entidad
@endsection

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
        <h1>Crear Entidad</h1>
        <form method="POST" action="{{ route('typesentity.store') }}">
            @csrf
            @include('typesentity._form', [ 'btnText' => 'Crear' ])
        </form>
    </main>
@endsection