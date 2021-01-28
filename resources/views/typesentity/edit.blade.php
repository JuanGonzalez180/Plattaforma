@extends('layout')

@section('title')
    Editar Entidad
@endsection

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
        <h1>Editar Entidad</h1>
        <form method="POST" action="{{ route('typesentity.update', $typeEntity) }}">
            @csrf @method('PATCH')
            @include('typesentity._form', [ 'btnText' => 'Editar' ])
        </form>
    </main>
@endsection