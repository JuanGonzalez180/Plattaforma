@extends('layout')

@section('title')
    Dashboard
@endsection


@section('content')
    @guest
        <div class="row row-login">
            <div class="col plt-center bg-login min-vh-100" style="background-image: url({{ asset('/assets/images/fondo.png') }});">
                <div class="w-100 text-center">
                    <img src="{{ asset('/assets/images/logo.svg') }}" />
                </div>
                <footer class="w-100">&copy;2020 PLATTAFORMA</footer>
            </div>
            <div class="col plt-center min-vh-100">
                <div class="forms form-sign-up w-100">
                    <h4>Iniciar</h4>
                    <h1>Iniciar sesión</h1>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group">
                            <label for="email" >{{ __('auth.email') }}</label>
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
                        
                        <div class="form-group">
                            <label for="password" class="">{{ __('auth.password') }}</label>
                            <div class="input-group input-group-sbr mb-3">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="{{ __('auth.password_placeholder') }}">
                                <div class="input-group-append form-btn-eye">
                                    <span class="input-group-text" id="basic-addon1"><img src="{{ asset('/assets/images/eye-line.svg')}}" /></span>
                                </div>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        {{ __('auth.remember-me') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col mr-3 text-right ft-normal">
                                @if (Route::has('password.request'))
                                    <a class="" href="{{ route('password.request') }}">
                                        {{ __('auth.forgot-your-password') }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="form-button">
                            <button type="submit" class="btn btn-primary w-100">{{ __('auth.login') }} <img class="icon-btn" src="{{ asset('/assets/images/send.svg')}}" /></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        
    @endguest
@endsection