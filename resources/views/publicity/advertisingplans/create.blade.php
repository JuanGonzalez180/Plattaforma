@extends('layout')

@section('title')
Planes Stripe
@endsection

@section('content')
@include('partials.structure.open-main')
<h1>Plan publicidad</h1>
<form method="post" action="{{route('publicity_plan.store')}}" enctype="multipart/form-data">
    <div class="form-row">
        @csrf

        @include('publicity.advertisingplans._form')


        <div class="col-md-12">
            <div class="form-group">
                <br>
                <label>Selecciona un plan de imagen</label>
                @forelse ($imagesPlans as $imagePlan)
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <input class="form-check-input" type="checkbox" name="img_plan[]" value="{{ $imagePlan->id }}">
                            <i class="fas fa-mobile-alt"></i>&nbsp;<b>Nombre:</b> {{ $imagePlan->name }}
                        </div>
                        <div class="col">
                            <i class="fas fa-mobile-alt"></i>&nbsp;<b>Tipo:</b> {{ $imagePlan->type }}
                        </div>
                        <div class="col">
                            <i class="fas fa-compress-alt"></i>&nbsp;<b>Ancho:</b> {{ $imagePlan->width }}
                        </div>
                        <div class="col">
                            <i class="fas fa-compress-alt"></i>&nbsp;<b>Alto:</b> {{ $imagePlan->high }}
                        </div>
                    </div>
                </div>
                <hr>
                @empty
                <div class="alert alert-light" role="alert">
                    No hay tipos de imagenes registradas
                </div>
                @endforelse
            </div>
            <br>
        </div>


    </div>
    <a type="button" class="btn btn-danger" href="{{ route('publicity_plan.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
    <button type="submit" class="btn btn-primary"><span class="oi oi-check" title="@lang('lang.save')" aria-hidden="true"></span> @lang('lang.save')</button>
</form>
<div class="mb-5"></div>
@include('partials.structure.close-main')
@endsection