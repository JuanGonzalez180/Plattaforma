@extends('layout')

@section('title')
    Crear Categoría del Servicio
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h2>Crear Categoría del Servicio</h2>
        <hr>
        <form method="POST" action="{{ route('categoryservices.store') }}" enctype="multipart/form-data">
            <div class="form-row">
                @csrf
                
                @include('categoryservices._form')
            </div>
            <a type="button" class="btn btn-danger" href="{{ route('categoryservices.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
            <button type="submit" class="btn btn-primary"><span class="oi oi-check" title="@lang('lang.save')" aria-hidden="true"></span> @lang('lang.save')</button>
        </form>
        <div class="mb-5"></div>
    @include('partials.structure.close-main')
@endsection