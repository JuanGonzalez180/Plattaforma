@extends('layout')

@section('title')
Planes Stripe
@endsection

@section('content')
@include('partials.structure.open-main')
<h1>Editar imagen plan</h1>
<form method="PUT" action="{{route('img_publicity_plan.update', $plan->id)}}" enctype="multipart/form-data">
    <div class="form-row">
        @csrf


        @include('publicity.imagesadvertisingplan._form')
    </div>
    <a type="button" class="btn btn-danger" href="{{ route('img_publicity_plan.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
    <button type="submit" class="btn btn-primary"><span class="oi oi-check" title="@lang('lang.save')" aria-hidden="true"></span> @lang('lang.save')</button>
</form>
<div class="mb-5"></div>
@include('partials.structure.close-main')
@endsection