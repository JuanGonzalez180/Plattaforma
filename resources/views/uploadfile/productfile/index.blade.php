
@extends('layout')

@section('title')
    Plantilla - Producto CSV
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>Plantilla - Producto Excel</h1>
        </div>
    </div>
    <hr>
    @include('partials.session-status')

    <form method="POST" action="{{ route('template-product-file.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-row">

            <div class="col-md-12">
                <div>
                    @if ( $existFile )
                        Archivo: 
                        <a href="{{$routeFileFull}}" target="_blank"> {{ $fileName }} </a>
                    @endif 
                </div>

                <div class="form-group">
                    <label>Selecciona una archivo de formato xlsx</label><br>
                    @if(session()->get('success'))
                        <div class="alert alert-success">
                            {{ session()->get('success') }}
                        </div>
                    @endif
                    <input type="file" name="template" placeholder="Selecciona la plantilla" id="template">
                    @error('template')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>
  
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-file-upload"></i> Subir Archivo</button>
    </form>
    
    @include('partials.structure.close-main')

@endsection
