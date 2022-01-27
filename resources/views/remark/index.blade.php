
@extends('layout')

@section('title')
    Reseñas
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>Reseñas
            </h1>
            {!!$header!!}
        </div>
    </div>
    @if(session()->get('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
    @endif
    <hr>
    @include('partials.session-status')

    <table id="myTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Mensaje</th>
                    <th scope="col">Calificación</th>
                    <th scope="col">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($remarks as $remark)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>
                            <p>
                                {{$remark->user->username}}
                                <br>
                                <b>Compañia | {{$remark->company->name}}</b>
                            </p>
                        </td>
                        <td>
                            <textarea class="form-control" rows="4" disabled>{{$remark->message}}</textarea>
                        </td>
                        <td>
                            @for ($i = 1; $i <=5; $i++)
                                @if($i <= $remark->calification)
                                <i class="fas fa-star"></i>
                                @else
                                <i class="far fa-star"></i>
                                @endif
                            @endfor
                            <span class="badge badge-primary">{{$remark->calification}}</span>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{$remark->status}}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No hay elementos</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @include('partials.structure.close-main')
@endsection

