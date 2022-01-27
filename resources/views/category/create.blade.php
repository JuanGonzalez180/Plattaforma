@extends('layout')

@section('title')
    Crear Categoría
@endsection

@section('content')
    @include('partials.structure.open-main')
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus"></i> Crear categoria
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('category.store') }}" enctype="multipart/form-data">
                    <div class="form-row">
                        @csrf
                        
                        @include('category._form')
                    </div>
                    <a type="button" class="btn btn-danger" href="{{ route('category.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
                    <button type="submit" class="btn btn-primary"><span class="oi oi-check" title="@lang('lang.save')" aria-hidden="true"></span> @lang('lang.save')</button>
                </form>
            </div>
        </div>

        <div class="mb-5"></div>
    @include('partials.structure.close-main')
@endsection