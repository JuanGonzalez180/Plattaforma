
@extends('layout')

@section('title')
    Blogs
@endsection

@section('content')
    @include('partials.structure.open-main')
    <div class="row align-items-center">
        <div class="col">
            <h1>Blogs</h1>
        </div>
    </div>
    <hr>
    @include('partials.session-status')

    @if(session()->get('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
    @endif

    <table id="myTable" class="table table-striped">
        <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">compañia</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($blogs as $blog)
                    <tr>
                        <td scope="row">{{ $loop->iteration }}</td>
                        <td>{{ $blog->name }}</td>
                        <td>{{ $blog->user->username }}</td>
                        <td>{{ $blog->company->name }}</td>
                        <td>
                            @if( $blog->status == 'Publicado')
                                <span class="badge badge-success"><i class="fas fa-check"></i> {{ $blog->status }}</span>
                            @else
                                <span class="badge badge-danger"><i class="fas fa-eraser"></i> {{ $blog->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a type="button" href="" class="btn btn-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay elementos</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @include('partials.structure.close-main')

@endsection
