@extends('layout')

@section('title')
@lang('Create') @lang('Content')
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>@lang('Create') @lang('Content')</h1>
        <form method="POST" action="{{ route('staticcontent.store') }}">
            @csrf
            @include('staticcontent._form',[
                'btnTextPrimary' => __('Save'),
                'btnTextCancel' => __('Cancel')
            ])
        </form>
    @include('partials.structure.close-main')
@endsection