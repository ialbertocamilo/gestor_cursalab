@extends('layouts.appfront')

@section('morecss')
<style>

.label-material.active{
  color:#007aff;
}
</style>
@endsection

@section('content')
<div class="page login-page d-flex justify-content-end align-items-center">
  <div class="content_login_form">
    <div class="form-holder has-shadow">
      <div class="logo mt-1 mx-auto text-center">
        <img src="{{ url('img/logo_cursalab_v2_black.png') }}" alt="cursalab" class="img-fluid" width="270">
      </div>
      <div class="px-4 mt-4 mx-3">
        <p>
          ¡Hola!
          <span class="text-primary">
            {{ auth()->user()->name.' '.auth()->user()->lastname }}
          </span>, para continuar por favor actualiza tu contraseña.
        </p>

        <p>
          Elige una contraseña segura y no la utilices en otras cuentas, ni la compartas con nadie.
        </p>
      </div>

      <div class="form mt-2">
        <form method="POST" class="form-validate" autocomplete="off" action="{{ route('password_reset') }}">
          @csrf
          <input hidden name="token" value="{{ $token }}" >
          <input hidden name="email" value="{{ auth()->user()->email }}" >

          <div class="form-group">
            <input id="reset-password" type="password" name="password"
                required data-msg="Por favor ingrese su nueva contraseña"
                class="input-material form-control{{ $errors->has('password') ? ' is-invalid' : '' }} no-icon one" autocomplete="off" maxlength="100" autofocus>
            <div class="one toggle-eye text-muted"
                onclick="toggleEyeInputId(this, 'reset-password')">
              <span class="far fa-eye fa-lg"></span>
            </div>

            <label for="reset-password" class="label-material active">Nueva contraseña</label>
            @if ($errors->has('password'))
            <span class="invalid-feedback" role="alert">
              <strong>{{ $errors->first('password') }}</strong>
            </span>
            @endif
          </div>

          <div class="form-group">
            <input id="reset-repassword" type="password" name="repassword"
                required data-msg="Por favor repita su nueva contraseña"
                class="input-material form-control{{ $errors->has('repassword') ? ' is-invalid' : '' }} no-icon two" autocomplete="off" maxlength="100">
            <div class="two toggle-eye text-muted"
                 onclick="toggleEyeInputId(this, 'reset-repassword')">
              <span class="far fa-eye fa-lg"></span>
            </div>

            <label for="reset-repassword" class="label-material">Repetir nueva contraseña</label>
            @if ($errors->has('repassword'))
            <span class="invalid-feedback" role="alert">
              <strong>{{ $errors->first('repassword') }}</strong>
            </span>
            @endif
          </div>

          <div class="form-group mb-0 text-center">
            <button type="submit" class="btn btn-primary">
              Actualizar contraseña
            </button>
          </div>

          <p class="mb-0 mt-4">
            <span class="font-weight-bold">
              ¿Por qué veo esto?
            </span>
            {{ $message }}
          </p>

        </form>
      </div>

    </div>
    <div class="mt-4 text-right copy">
      <a href="https://cursalab.io/" target="_blank" class="external">
        <img src="{{ url('img/poweredByCursalab_v2.png') }}" alt="powerby-cursalab" class="img-fluid" width="120">
      </a>
    </div>
  </div>
</div>

@endsection
