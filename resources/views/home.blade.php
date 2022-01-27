@extends('layout')

@section('title')
    Dashboard
@endsection


@section('content')
    @guest
        Iniciar sesión
    @else
        
    @endguest
@endsection