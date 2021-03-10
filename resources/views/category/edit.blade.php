@extends('layout')

@section('title')
    Editar Categoría
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Editar Categoría</h1>
        <form method="POST" action="{{ route('category.update', $category->id) }}" enctype="multipart/form-data">
            <div class="form-row">
                @csrf @method('PATCH')
                
                @include('category._form')
            </div>
            <a type="button" class="btn btn-danger" href="{{ route('category.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
            <button type="submit" class="btn btn-primary">@lang('lang.save')</button>
        </form>
        <div class="mb-5"></div>
    @include('partials.structure.close-main')
@endsection