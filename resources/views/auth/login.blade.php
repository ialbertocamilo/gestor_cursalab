@extends('layouts.appfront')

@section('morecss')
<style>

</style>
@endsection

@section('content')
<div class="page login-page d-flex justify-content-end align-items-center">
  <div class="content_login_form">
    <div class="form-holder has-shadow">
      <div class="logo mt-5">
        <img src="img/logo.png" alt="..." class="img-fluid">
      </div>

      <div class="titulo text-center mt-5 mb-5">
        <h2>Bienvenido a</h2>
        <h1>Cursalab Gestiona</h1>
      </div>

      <div class="form mt-5">
        <form method="POST" class="form-validate" action="{{ route('login_post') }}">
          @csrf
          <div class="form-group">
            <input id="login-username" type="text" name="email" required data-msg="Please enter your username" class="input-material form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" autocomplete="off">
            <label for="login-username" class="label-material active">{{ __('Email') }}</label>
            @if ($errors->has('email'))
            <span class="invalid-feedback" role="alert">
              <strong>{{ $errors->first('email') }}</strong>
            </span>
            @endif
          </div>
          <div class="form-group">
            <input id="login-password" type="password" name="password" required data-msg="Please enter your password" class="input-material form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" autocomplete="off">
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
        <img src="img/poweredByCursalab.png" alt="..." class="img-fluid">
      </a>
    </div>
  </div>
</div>
@endsection