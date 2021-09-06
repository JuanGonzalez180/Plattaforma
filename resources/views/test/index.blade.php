
@extends('layout')

@section('title')
    test
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>TEST</h1>
        </div>
    </div>
    <hr>
    @include('partials.session-status')

    <form method="POST" action="{{ route('testing.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-row">

        <div class="col-md-12">
            <div class="form-group">
                <label>Selecciona una imagen</label><br>
                <input type="file" name="file_cvs" placeholder="Selecciona el cvs" id="image">
            </div>
        </div>
  
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
    
    @include('partials.structure.close-main')

@endsection
