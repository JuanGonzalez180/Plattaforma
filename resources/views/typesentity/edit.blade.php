@extends('layout')

@section('title')
    Editar Entidad
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Editar Entidad</h1>
        <form method="POST" action="{{ route('typesentity.update', $typeEntity) }}">
            @csrf @method('PATCH')
            @include('typesentity._form',[
                'btnTextPrimary' => __('Edit'),
                'btnTextCancel' => __('Cancel')
            ])
        </form>
    @include('partials.structure.close-main')
@endsection