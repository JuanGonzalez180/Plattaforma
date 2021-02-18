@extends('layout')

@section('title')
@lang('Create') @lang('Country')
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>@lang('Create') @lang('Country')</h1>
        <form method="POST" action="{{ route('countries.store') }}">
            @csrf
            @include('countries._form',[
                'btnTextPrimary' => __('Save'),
                'btnTextCancel' => __('Cancel')
            ])
        </form>
    @include('partials.structure.close-main')
@endsection