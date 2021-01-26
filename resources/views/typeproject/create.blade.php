@extends('layout')

@section('title')
    Crear Tipo de Proyecto
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Crear Tipo de Proyecto</h1>
        <form method="POST" action="{{ route('typeproject.store') }}">
            <div class="form-row">
                @csrf
                
                @include('typeproject._form')
            </div>
            <a type="button" class="btn btn-danger" href="{{ route('typeproject.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
            <button type="submit" class="btn btn-primary"><span class="oi oi-check" title="@lang('lang.save')" aria-hidden="true"></span> @lang('lang.save')</button>
        </form>
    @include('partials.structure.close-main')
@endsection