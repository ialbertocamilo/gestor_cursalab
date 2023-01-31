@extends('layouts.appfront')

@section('morecss')
<style>
.reset-page{
    position: relative;
    min-height: 100vh;
}
.login-page::before{
    content:'';
}
</style>
@endsection

@section('content')
    
    @include('layouts.user-header')

    <div class="login-page d-flex justify-content-end align-items-center">
      <div class="content_login_form">
        <div class="form-holder has-shadow">
          <div class="logo mt-5 mx-auto text-center">
            <img src="img/logo_cursalab_v2_black.png" alt="cursalab" class="img-fluid" width="270">
          </div>
          <div class="px-4 mt-5 mx-3 text-center">
            <p>
              <span class="{{$errors->has('resend') ? 'font-weight-bold' : ''}}">
                {{ $errors->has('resend') ? $errors->first('resend') : 'Hemos enviado' }}
              </span> 
              un código al correo 
              <span class="text-primary">{{ auth()->user()->email }}</span>, 
              por favor revise su bandeja de entrada.
            </p>
          </div>
          <div class="form">
            <form method="POST" class="form-validate" action="{{ route('login_auth2fa') }}">
              @csrf
              <div class="form-group">
                <input id="login-code" type="text" name="code" 
                      required 
                      data-msg="Por favor ingrese su código" 
                      class="input-material text-lg text-center form-control{{ $errors->has('code') ? ' is-invalid' : '' }}" 
                      autocomplete="off"
                      maxlength="20"
                      autofocus >
                
                <label for="login-code" class="label-material active">Código</label>
                
                @if ($errors->has('code'))
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $errors->first('code') }}</strong>
                </span>
                @endif

              </div>

              <div class="form-row">
                <div class="col-6 text-center">
                  <a href="{{ route('login_auth2fa_resend') }}" class="text-primary text-decoration-none py-2">
                    Reenviar
                  </a>
                </div>
                <div class="col-6">
                  <button type="submit" class="btn py-2 btn-block btn-primary">
                    Verificar
                  </button>
                </div>
              </div>

              <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-primary"> Ir a inicio de sesión.</a>
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
