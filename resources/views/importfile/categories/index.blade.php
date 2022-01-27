@extends('layout')

@section('title')
Categorias - Archivo TXT
@endsection

@section('content')
@include('partials.structure.open-main')
<div class="row align-items-center">
    <div class="col">
        <h1>Importar - Categorias</h1>
    </div>
</div>
<hr>
@if(session()->get('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif
@include('partials.session-status')
<form method="POST" action="{{ route('file-company-txt.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="form-row">

        <div class="col-md-12">
            <div class="form-group">
                <label>Selecciona una archivo de formato .txt</label><br>
                @if(session()->get('success'))
                <div class="alert alert-success">
                    {{ session()->get('success') }}
                </div>
                @endif
                <input type="file" name="file_txt" placeholder="Selecciona un archivo .txt">
                @error('file_txt')
                <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
        </div>

    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-file-upload"></i> Subir Archivo</button>
</form>



@include('partials.structure.close-main')

@endsection