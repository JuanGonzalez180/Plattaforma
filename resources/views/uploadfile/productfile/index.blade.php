
@extends('layout')

@section('title')
    Plantilla - Producto CSV
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>Plantilla - Producto CSV</h1>
        </div>
    </div>
    <hr>
    @include('partials.session-status')

    <form method="POST" action="{{ route('template-product-file.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-row">

        <div class="col-md-12">
            <div class="form-group">
                <label>Selecciona una imagen</label><br>
                <input type="file" name="template" placeholder="Selecciona la plantilla" id="template">
            </div>
        </div>
  
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
    
    @include('partials.structure.close-main')

@endsection
