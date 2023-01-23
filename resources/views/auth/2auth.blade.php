@extends('layouts.appfront')

@section('content')
  <div>
    <span> Se ha enviado un codigo de verificacion a su correo. </span>
  </div>
  <form method="POST" class="form-validate" action="{{ route('login_2auth') }}">
    @csrf
    <div class="form-group">
      <input id="login-2auth" type="text" name="2auth" required 
            data-msg="Por favor ingrese su codigo de verificación" 
            class="input-material form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" 
            autocomplete="off">
      
      <label for="login-2auth" class="label-material active">{{ __('Email') }}</label>
      @if ($errors->has('code'))
      <span class="invalid-feedback" role="alert">
        <strong>{{ $errors->first('code') }}</strong>
      </span>
      @endif
    </div>

    <div class="form-group text-center">
      <button type="submit" class="btn btn-secondary">
        {{ __('Reenviar') }}
      </button>
      <button type="submit" class="btn btn-primary">
        {{ __('Verificar') }}
      </button>
    </div>
  </form>
@endsection
