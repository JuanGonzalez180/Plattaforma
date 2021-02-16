@extends('layout')

@section('title')
    @lang('Edit') @lang('Country')
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>@lang('Edit') @lang('Country')</h1>
        <form method="POST" action="{{ route('countries.update', $country) }}">
            @csrf @method('PATCH')
            @include('countries._form',[
                'btnTextPrimary' => __('Edit'),
                'btnTextCancel' => __('Cancel')
            ])
        </form>
    @include('partials.structure.close-main')
@endsection