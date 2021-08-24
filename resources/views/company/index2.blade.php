@extends('layout')

@section('title')
    Categorías del servicio
@endsection


@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Compañias</h1>
            </div>
        </div>
        <hr>
        <div class="form-group col-md-6">
            <label for="parent_id">Tipo</label>
            <select name="parent_id" id="parent_id" class="form-control form-control-sm" onchange="getCategoryChilds(this.value);">
            @foreach($types as $type)
                <option value="{{$type->id}}">{{$type->name}}</option>
            @endforeach
            </select>
        </div>
    @include('partials.structure.close-main')

@endsection