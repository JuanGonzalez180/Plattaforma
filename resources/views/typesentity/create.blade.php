@extends('layout')

@section('title')
    Crear Entidad
@endsection

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
        <h1>Crear Entidad</h1>
        <form method="POST" action="{{ route('typesentity.store') }}">
            <div class="form-row">
                @csrf
                <div class="form-group col-md-6">
                    <label for="inputName">@lang('lang.name')</label>
                    <input type="text" class="form-control" name="inputName" id="inputName" placeholder="@lang('lang.name')">

                    @error('inputName')
                        <span class="text-danger" role="alert">
                            <small><b>{{ $errors->getBag('default')->first('inputName') }}</b></small>
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="inputType">@lang('Type')</label>
                    <select name="inputType" id="inputType" class="form-control">
                        <option value="" selected>@lang('Type')</option>
                        @foreach ($typeOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                    @error('inputType')
                        <span class="text-danger" role="alert">
                            <small><b>{{ $errors->getBag('default')->first('inputType') }}</b></small>
                        </span>
                    @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">@lang('Create')</button>
        </form>
    </main>
@endsection