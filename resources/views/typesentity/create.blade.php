@extends('layout')

@section('title')
    Crear Entidad
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Crear Entidad</h1>
        <form method="POST" action="{{ route('typesentity.store') }}">
            @csrf
            @include('typesentity._form',[
                'btnTextPrimary' => __('Save'),
                'btnTextCancel' => __('Cancel')
            ])
        </form>
    @include('partials.structure.close-main')
@endsection