@extends('layout')

@section('title')
    Productos Stripe
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Productos Stripe</h1>
        <form action="{{route('products_stripe.store')}}" method="post">
            <div class="form-row">
                @csrf
                
                @include('products_stripe._form')
            </div>
            <a type="button" class="btn btn-danger" href="{{ route('products_stripe.index') }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
            <button type="submit" class="btn btn-primary"><span class="oi oi-check" title="@lang('lang.save')" aria-hidden="true"></span> @lang('lang.save')</button>
        </form>
        <div class="mb-5"></div>
    @include('partials.structure.close-main')
@endsection