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
    </div>
    <a type="button" class="btn btn-danger" href="{{ route('publicity_plan.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
    <button type="submit" class="btn btn-primary"><span class="oi oi-check" title="@lang('lang.save')" aria-hidden="true"></span> @lang('lang.save')</button>
</form>
<div class="mb-5"></div>
@include('partials.structure.close-main')
@endsection