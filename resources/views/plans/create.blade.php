@extends('layout')

@section('title')
    Planes Stripe
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Planes Stripe</h1>
        <form action="{{route('plans.store')}}" method="post">
            <div class="form-row">
                @csrf
                
                @include('plans._form')
            </div>
            <a type="button" class="btn btn-danger" href="{{ route('plans.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
            <button type="submit" class="btn btn-primary"><span class="oi oi-check" title="@lang('lang.save')" aria-hidden="true"></span> @lang('lang.save')</button>
        </form>
        <div class="mb-5"></div>
    @include('partials.structure.close-main')
@endsection