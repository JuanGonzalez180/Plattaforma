
@extends('layout')

@section('title')
    Usuarios
@endsection

@section('content')
    @include('partials.structure.open-main')
        <div class="row align-items-center">
            <div class="col">
                <h1>Usuarios</h1>
            </div>
        </div>

        @include('partials.session-status')
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Compañia</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $user->username }}</td>
                        @if( $user->company && $user->company->first() )
                            <td> {{ ($user->company ) ? $user->company->first()['name'] : '' }} </td>
                            <td> 
                                @if($user->company && $user->company->first()['type_entity_id'] == 1 )
                                    Demanda
                                @endif
                                @if($user->company && $user->company->first()['type_entity_id'] == 2 )
                                    Oferta
                                @endif
                            </td>
                            <td>
                                <a type="button" href="{{ route( 'users.edit', $user ) }}" class="btn btn-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
                                @if($user->company && $user->company->first()['status'] == 'Creado' )
                                    <form method="POST" action="{{ route( 'users.approve', $user ) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$user->id}}"/>
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas Activar el usuario?')" data-toggle="tooltip" title='Activar'> <i class="oi oi-check"> </i></button>
                                    </form>
                                @endif
                            </td>
                        @else 
                            <td></td>
                            <td></td>
                            <td></td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No hay elementos</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center mb-5">
            {!! $users->links() !!}
        </div>

    @include('partials.structure.close-main')
@endsection
