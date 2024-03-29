@extends('layout')

@section('title')
    Editar Categoría del servicio
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h2>Editar Categoría del servicio</h2>
        <hr>
        <form method="POST" action="{{ route('categoryservices.update', $category->id) }}" enctype="multipart/form-data">
            <div class="form-row">
                @csrf @method('PATCH')
                
                @include('categoryservices._form')
            </div>
            <a type="button" class="btn btn-danger" href="{{ route('categoryservices.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
            <button type="submit" class="btn btn-primary">@lang('lang.save')</button>
        </form>
        <div class="mb-5"></div>
    @include('partials.structure.close-main')
@endsection