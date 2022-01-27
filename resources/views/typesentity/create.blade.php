@extends('layout')

@section('title')
    Crear Entidad
@endsection

@section('content')
    @include('partials.structure.open-main')
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus"></i> Crear entidad
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('typesentity.store') }}">
                    @csrf
                    @include('typesentity._form',[
                        'btnTextPrimary' => __('Save'),
                        'btnTextCancel' => __('Cancel')
                    ])
                </form>
            </div>
        </div>
    @include('partials.structure.close-main')
@endsection