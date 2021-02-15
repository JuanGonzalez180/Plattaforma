@extends('layout')

@section('title')
    Usuario
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Usuario</h1>
        {{ $user }}
        {{ $user->id }}
    @include('partials.structure.close-main')
@endsection