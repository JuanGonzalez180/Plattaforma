@extends('layout')

@section('title')
    {{ ucwords($product->type) }}
@endsection

@section('content')
    @include('partials.structure.open-main')

    <a href="{{ url()->previous() }}" class="link-primary"><span class="oi oi-arrow-left" title="Ver" aria-hidden="true"></span> Atras</a>
    
    <h1>{{ ucwords($product->type) }}</h1>

    <dl class="row">
        <dt class="col-sm-4">Nombre:</dt>
        <dd class="col-sm-8">{{$product->name}}</dd>
        <dt class="col-sm-4">Compañia:</dt>
        <dd class="col-sm-8">{{$product->company->name}}</dd>
        <dt class="col-sm-4">Marca:</dt>
        <dd class="col-sm-8">{{$product->brand->name}}</dd>
        <dt class="col-sm-4">Descripción:</dt>
        <dd class="col-sm-8">
            @if(!is_null($product->description))
                {{$product->description}}
            @else
                <span class="badge badge-secondary">Sin descripción</span>
            @endif
        </dd>
        <dt class="col-sm-4">Tipo:</dt>
        <dd class="col-sm-8">{{$product->type}}</dd>
        <dt class="col-sm-4">Estado:</dt>
        <dd class="col-sm-8">{{$product->status}}</dd>
        @if(count($product->tags) > 0)
        <dt class="col-sm-4">Etiquetas:</dt>
        <dd class="col-sm-8">
            @foreach($product->tags as $tag)
            <span class="badge badge-info">{{$tag->name}}</span>
            @endforeach
        </dd>
        @endif

        @if(count($product->productCategories) > 0)
        <dt class="col-sm-4">Categorias:</dt>
        <dd class="col-sm-8">
            @foreach($product->productCategories as $category)
                @if($category->status == 'Publicado')
                    <span class="badge badge-info">{{$category->name}}</span>
                @endif
            @endforeach
        </dd>
        @endif
    </dl>

        
    @include('partials.structure.close-main')
@endsection