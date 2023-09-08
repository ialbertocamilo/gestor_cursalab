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
      <div class="logo mt-5 mx-auto text-center">
        <img src="img/logo_cursalab_v3_black.png" alt="cursalab" class="img-fluid" width="270">
      </div>
      <div class="titulo text-center mt-3 mb-1">
        <h3>¡Bienvenido!</h3>

        <div class="mt-4 {{ $errors->has('attempts_fulled') ? 'text-danger' : '' }}">
          @if($errors->has('attempts_fulled') && $errors->first('attempts_count') == $errors->first('attempts_max'))
            <p class="mb-0">Por favor vuelve a intentarlo dentro de <span id="decrement-animation">{{ $errors->first('current_time') }}</span></p>
          @endif

          @if($errors->has('attempts_fulled') && !$errors->first('attempts_fulled'))
            <p class="mb-0">Has realizado {{ $errors->first('attempts_count') }} de {{ $errors->first('attempts_max') }} intentos </p>
          @endif
        </div>
      </div>

      <div class="form mt-1">
        <form method="POST" class="form-validate" action="{{ route('login_post') }}">
          @csrf
          <div class="form-group">
            <input id="login-username" type="text" name="email" required data-msg="Por favor ingrese su email" class="input-material form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" autocomplete="off" autofocus>
            <label for="login-username" class="label-material active">{{ __('Email') }}</label>
            @if ($errors->has('email'))
            <span class="invalid-feedback" role="alert">
              <strong>{{ $errors->first('email') }}</strong>
            </span>
            @endif
          </div>

          <div class="form-group">
            <input id="login-password" type="password" name="password" required data-msg="Por favor ingrese su contraseña" class="input-material form-control{{ $errors->has('password') ? ' is-invalid' : '' }} no-icon one" autocomplete="off">
            <div class="one toggle-eye text-muted" onclick="toggleEyeInputId(this, 'login-password')">
              <span class="far fa-eye fa-lg"></span>
            </div>
            <label for="login-password" class="label-material">{{ __('Contraseña') }}</label>

            @if ($errors->has('password'))
            <span class="invalid-feedback" role="alert">
              <strong>{{ $errors->first('password') }}</strong>
            </span>
            @endif

          </div>
          <!-- <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                            <label class="form-check-label" for="remember" role="button">
                                <small>{{ __('Recordarme') }}</small>
                            </label>
                        </div>
                    </div> -->
          <div class="form-group text-center">

            @if($errors->has('attempts_fulled') && $errors->first('attempts_count') == $errors->first('attempts_max'))

              <button type="submit" class="btn btn-primary" id="decrement-timeout-disabled" disabled>
                {{ __('Ingresar') }}
              </button>

            @else

              <button type="submit" class="btn btn-primary">
                {{ __('Ingresar') }}
              </button>

            @endif


            <br>
            <a href="{{ route('password.update') }}" target="_self" rel="noopener noreferrer" style="color:#0000D2" class="pt-4">Olvidé mi contraseña</a>
          </div>
        </form>
      </div>

    </div>
    <div class="mt-4 text-right copy">
      <a href="https://cursalab.io/" target="_blank" class="external">
        <img src="img/poweredByCursalab_v2.png" alt="..." class="img-fluid" width="120">
      </a>
    </div>
  </div>
</div>
<script>
    {{-- Reloads the login page, to avoid error 419 --}}

    setTimeout(() => {
        location.reload();
    }, 20 * 60 * 1000);
</script>
@endsection
