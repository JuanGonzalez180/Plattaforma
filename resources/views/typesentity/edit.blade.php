@extends('layout')

@section('title')
    Editar Entidad
@endsection

@section('content')
    @include('partials.structure.open-main')
        <div class="card">
            <div class="card-header">
            <i class="oi oi-pencil"></i> Editar Entidad
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('typesentity.update', $typeEntity) }}">
                    @csrf @method('PATCH')
                    @include('typesentity._form',[
                        'btnTextPrimary' => __('Edit'),
                        'btnTextCancel' => __('Cancel')
                    ])
                </form>
            </div>
        </div>
    @include('partials.structure.close-main')
@endsection