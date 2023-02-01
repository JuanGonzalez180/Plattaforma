@extends('layout')

@section('title')
    Editar Entidad de la compañia
@endsection

@section('content')
    @include('partials.structure.open-main')
        <div class="card">
            <div class="card-header">
            <i class="oi oi-pencil"></i> Editar Entidad
            </div>
            <div class="card-body">
                <form>
                    <div class="form-row">
                        <input type="hidden" id="company_id" name="company_id" value="{{$company->id}}" />
                        <div class="form-group col-md-12">
                            <label>Compañia | <h5>{{$company->name}}</h5></label>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Entidad</label>
                            <select name="entity_id" id="entity_id" class="form-control">
                                @foreach ($entitylist as $option)
                                <option value="{{ $option['id'] }}" {{  $company->type_entity_id == $option['id'] ? 'selected' : '' }} >{{ $option['name'] }}</option>
                                @endforeach
                            </select>
                            
                        </div>
                    </div>
                    <a type="button" class="btn btn-danger" href=""><span class="oi oi-x" aria-hidden="true"></span> Cancelar </a>
                    <button type="submit" class="btn btn-primary"><span class="oi oi-check" aria-hidden="true"></span>Guardar</button>
                </form>
            </div>
        </div>
    @include('partials.structure.close-main')
@endsection