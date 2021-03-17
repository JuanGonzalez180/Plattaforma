@extends('layout')

@section('title')
    Productos Stripe
@endsection


@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Productos Stripe</h1>
            </div>
            <div class="col text-right">
                <a type="button" class="btn btn-primary" href="{{ route('products_stripe.create') }}"><span class="oi oi-plus" title="Nuevo" aria-hidden="true"></span> Crear Producto</a>
            </div>
        </div>
        @if(session()->get('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">Productos</div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($products_stripe as $product)
                    <li class="list-group-item clearfix">
                        <div class="pull-left">
                            <h5>{{ $product->name }}</h5>
                            {{-- <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-dark pull-right">Ver</a> --}}
                            <form method="POST" action="{{ route('products_stripe.destroy', $product->stripe_product ) }}" class="d-inline">
                                @method('DELETE')
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseas Eliminar el producto stripe?')" data-toggle="tooltip" title='Eliminar'> <i class="oi oi-trash"> </i> Eliminar </button>
                            </form>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @include('partials.structure.close-main')
@endsection