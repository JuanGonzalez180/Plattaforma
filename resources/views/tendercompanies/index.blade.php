
@extends('layout')

@section('title')
    Licitaciones
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>Compañias licitantes</h1>
        </div>
    </div>
    <hr>
    @include('partials.session-status')

    @if(session()->get('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
    @endif


    <table id="myTable" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Compañia</th>
                <th scope="col">Tipo</th>
                <th scope="col">Estado</th>
                <th scope="col">Precio</th>
                <th scope="col">Ganador</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tenderCompanies as $company)
                <tr>
                    <th scope="row">{{ $loop->iteration }}</th>
                    <td>{{$company->company->name}}</td>
                    <td>{{$company->type}}</td>
                    <td>{{$company->status}}</td>
                    <td>${{$company->price}}</td>
                    <td>
                        @if($company->winner == 'true')
                            <span class="badge badge-success">Ganador</span>
                        @else
                            <span class="badge badge-secondary">No definido</span>
                        @endif
                    </td>
                    <td>
                        <a type="button" href="{{ route('tender-companies.show', $company->id ) }}" class="btn btn-outline-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No hay elementos</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('partials.structure.close-main')
@endsection

