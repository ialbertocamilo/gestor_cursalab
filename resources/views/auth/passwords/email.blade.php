@extends('layouts.appfront')
@section('morecss')
    <style>
        .label-material.active {
            color: #007aff;
        }

        .color-text-b {
            color: #0000D2
        }
    </style>
@endsection
@section('content')
    <div class="page login-page d-flex justify-content-end align-items-center">
        <div class="content_login_form">
            <div class="form-holder has-shadow">
                @if (session('status'))
                    <section class="form mt-1">
                        <div class="titulo text-center mt-3 mb-1">
                            <h4 class="color-text-b">Hemos enviado un enlace para <br> recuperar tu contraseña</h4>
                        </div>
                        <div class="logo mt-10 mx-auto text-center" style="margin-top: 60px">
                            <img src="{{ url('/img/success_sent_email.svg') }}" alt="cursalab" class="img-fluid"
                                width="120">
                        </div>
                        <div class="text-center" style="margin-top: 25px">
                            <h5 class="text-bold">¡Revisa tu correo!</h5>
                            <p>En caso de no encontrarlo, recuerda <br> buscar en tu <strong>bandeja de spam.</strong></p>
                        </div>
                    </section>
                @else
                    <div class="titulo text-center mt-3 mb-1">
                        <h4 class="color-text-b">Queremos comprobar que eres tú</h4>
                        <p>Ingresa tu correo registrado</p>
                    </div>
                    <div class="form mt-1">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <div class="form-group row">
                                <input id="email" type="email"
                                    class="input-material form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                    name="email" value="{{ old('email') }}" required autocomplete="off" autofocus>

                                <label for="email" class="label-material active">{{ __('Email') }}</label>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Enviar correo de verificación') }}
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" target="_self" class="px-3 py-1 btn btn-outline-primary"
                        class="color-text-b"> <i class="pr-1 fas fa-home"></i> Volver al inicio</a>
                </div>
            </div>
            <div class="mt-4 text-right copy">
                <a href="https://cursalab.io/" target="_blank" class="external">
                    <img src="{{ url('/img/poweredByCursalab_v2.png') }}" alt="..." class="img-fluid" width="120">
                </a>
            </div>
        </div>
    </div>
@endsection
