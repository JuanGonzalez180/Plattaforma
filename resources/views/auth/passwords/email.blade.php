@extends('layout')

@section('title')
    Recuperar Contraseña
@endsection

@section('content')
<div class="row row-login">
    <div class="col plt-center bg-login min-vh-100" style="background-image: url({{ asset('/assets/images/fondo.png') }});">
        <div class="w-100 text-center">
            <img src="{{ asset('/assets/images/logo.svg') }}" />
        </div>
        <footer class="w-100">&copy;2020 PLATTAFORMA</footer>
    </div>
    <div class="col plt-center min-vh-100">
        <div class="forms form-sign-up w-100">
            <h4>Recuperar cuenta</h4>
            <h1>¿Olvidaste tu contraseña?</h1>
            <p>Ingrese su dirección de correo electrónico y le enviaremos un link para restablecer su contraseña.</p>

            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label for="email" class="">{{ __('auth.email') }}</label>
                    <div class="input-group input-group-pegado input-group-sbr mb-3">
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="{{ __('auth.email_placeholder') }}">
                        <div class="input-group-append">
                            <span class="input-group-text" id="basic-addon1"><img src="{{ asset('/assets/images/email.svg')}}" /></span>
                        </div>
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-button">
                    <button type="submit" class="btn btn-primary w-100">{{ __('auth.link_reset') }} <img class="icon-btn" src="{{ asset('/assets/images/send.svg')}}" /></button>
                </div>

                <div class="row">
                    <div class="col">
                    </div>
                    <div class="col mr-3 text-right ft-normal">
                        @if (Route::has('login'))
                            <a class="" href="{{ route('welcome') }}">
                                {{ __('auth.login') }}
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
