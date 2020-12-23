@extends('layout')

@section('title')
    Usuarios
@endsection


@section('content')
    @if( $users )
        <table>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user['name'] }}</td>
                </tr>
            @endforeach()
        </table>
    @else
        No hay usuarios
    @endif
@endsection