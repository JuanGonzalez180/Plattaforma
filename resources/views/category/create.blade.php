@extends('layout')

@section('title')
    Crear Categoría
@endsection

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
        <h1>Crear Categoría</h1>
        <form method="POST" action="{{ route('category.store') }}">
            <div class="form-row">
                @csrf
                <div class="form-group col-md-6">
                    <label for="name">@lang('lang.name')</label>
                    <input type="text" class="form-control" name="name" id="name" placeholder="@lang('lang.name')">

                    @error('name')
                        <span class="text-danger" role="alert">
                            <small><b>{{ $errors->getBag('default')->first('name') }}</b></small>
                        </span>
                    @enderror
                </div>

                <div class="form-group col-md-6">
                    <label for="parent_id">@lang('lang.parentCategory')</label>
                    <select name="parent_id" id="parent_id" class="form-control">
                        <option value="" selected>@lang('lang.parentCategory')</option>
                        @foreach ($categoryOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <span class="text-danger" role="alert">
                            <small><b>{{ $errors->getBag('default')->first('parent_id') }}</b></small>
                        </span>
                    @enderror
                </div>

                <div class="form-group col-md-12">
                    <label for="description">@lang('lang.description')</label>
                    <textarea type="text" class="form-control" name="description" id="description" placeholder="@lang('lang.description')"></textarea>

                    @error('description')
                        <span class="text-danger" role="alert">
                            <small><b>{{ $errors->getBag('default')->first('description') }}</b></small>
                        </span>
                    @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">@lang('Create')</button>
        </form>
    </main>
@endsection