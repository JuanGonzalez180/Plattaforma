@extends('layout')

@section('title')
    Usuarios
@endsection


@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
        @forelse($users as $user)
            <tr>
                <td>{{ $user['name'] }}</td>
            </tr>
        @empty
            No hay usuarios
        @endforelse
    </main>
@endsection