@extends('layout')

@section('title')
    Editar Plan Stripe
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Editar Plan Stripe</h1>
        <form method="POST" action="{{ route('plans.update', $plan->id) }}">
            <div class="form-row">
                @csrf @method('PATCH')
                
                @include('plans._form')
            </div>
            <a type="button" class="btn btn-danger" href="{{ route('plans.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
            <button type="submit" class="btn btn-primary">@lang('lang.save')</button>
        </form>
        <div class="mb-5"></div>
    @include('partials.structure.close-main')
@endsection