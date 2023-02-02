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
        <img src="img/logo_cursalab_v2_black.png" alt="cursalab" class="img-fluid" width="270">
      </div>
      <div class="titulo text-center mt-5 mb-5">
        <h3>¡Bienvenido!</h3>
        {{-- <h2><strong>Cursalab Gestiona</strong></h2> --}}
      </div>

      <div class="form mt-5">
        <form method="POST" class="form-validate" action="{{ route('login_post') }}">
          @csrf
          <div class="form-group">
            <input id="login-username" type="text" name="email" required data-msg="Por favor ingrese su email" class="input-material form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" autocomplete="off">
            <label for="login-username" class="label-material active">{{ __('Email') }}</label>
            @if ($errors->has('email'))
            <span class="invalid-feedback" role="alert">
              <strong>{{ $errors->first('email') }}</strong>
            </span>
            @endif
          </div>
          <div class="form-group">
            <input id="login-password" type="password" name="password" required data-msg="Por favor ingrese su contraseña" class="input-material form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" autocomplete="off">
            <label for="login-password" class="label-material active">{{ __('Contraseña') }}</label>
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
            <button type="submit" class="btn btn-primary">
              {{ __('Ingresar') }}
            </button>
            <br>
          </div>
        </form>
      </div>

    </div>
    <div class="mt-4 text-right copy">
      <a href="https://cursalab.io/" target="_blank" class="external">
        <img src="img/poweredByCursalab.png" alt="..." class="img-fluid" width="120">
      </a>
    </div>
  </div>
</div>
@endsection
