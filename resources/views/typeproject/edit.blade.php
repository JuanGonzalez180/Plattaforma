@extends('layout')

@section('title')
    Editar Tipo de Proyecto
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Editar Tipo de Proyecto</h1>
        <form method="POST" action="{{ route('typeproject.update', $typeproject->id) }}">
            <div class="form-row">
                @csrf @method('PATCH')
                
                @include('typeproject._form')
            </div>
            <a type="button" class="btn btn-danger" href="{{ route('typeproject.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
            <button type="submit" class="btn btn-primary">@lang('lang.save')</button>
        </form>
    @include('partials.structure.close-main')
@endsection