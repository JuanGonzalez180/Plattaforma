@extends('layout')

@section('title')
    @lang('Edit') @lang('Content')
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>@lang('Edit') @lang('Content')</h1>
        <form method="POST" action="{{ route('staticcontent.update', $staticContent) }}">
            @csrf @method('PATCH')
            @include('staticcontent._form',[
                'btnTextPrimary' => __('Edit'),
                'btnTextCancel' => __('Cancel')
            ])
        </form>
    @include('partials.structure.close-main')
@endsection