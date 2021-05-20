@extends('layout')

@section('title')
    Crear Marcar
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Crear Marca</h1>
        <form method="POST" action="{{ route('brand.store') }}" enctype="multipart/form-data">
            <div class="form-row">
                @csrf
                
                @include('brand._form')
            </div>
            <a type="button" class="btn btn-danger" href="{{ route('brand.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
            <button type="submit" class="btn btn-primary"><span class="oi oi-check" title="@lang('lang.save')" aria-hidden="true"></span> @lang('lang.save')</button>
        </form>
        <div class="mb-5"></div>
    @include('partials.structure.close-main')
@endsection