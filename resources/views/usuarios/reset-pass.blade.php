@extends('layouts.appfront')

@section('morecss')
<style>
  .label-material.active{
    color:#007aff;
  }

  .login-page{
    min-height: 90vh;
  }

  .login-page::before {
    content: "";
    width: 100%;
    height: 100%;
    background-size: cover; 
    position: absolute;
    top: 0;
    right: 0;
    background-color: snow; 
    background-image: none; 
    background-position: bottom left;
    background-repeat: no-repeat;
  }

  .alert-box-fixed-center {
    z-index: 1000;
    position: fixed;
    top: 9%;
    left: 30%;
    right: 30%;
  }

</style>

@endsection

@section('content')
  
  @include('layouts.user-header')
  
  @if (session('info'))
    <div class="alert-box-fixed-center">
      <div class="position-relative">
          <div class="alert alert-success alert-dismissible m-0" role="alert">
              {{ session('info') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                      aria-hidden="true">&times;</span></button>
          </div>
      </div>
    </div>
  @endif
  
  <div class="login-page d-flex justify-content-center align-items-center">
    <div class="content_login_form mr-0">
      <div class="form-holder has-shadow px-5">
        <div class="px-4 mb-5 mx-3 text-center">
          <h3 class="mb-3">Cambiar contraseña</h3>
          <p> 
            <span class="text-primary">
              {{ auth()->user()->name.' '.auth()->user()->lastname  }}
            </span>, recomendamos que tu contraseña sea única, y que no la compartas con nadie.</p>
        </div>

        <div class="form mt-2 p-0">
          <form method="POST" class="form-validate" autocomplete="off" action="{{ route('usuarios.user_password_reset') }}">
            @csrf

            <div class="form-group">
              <input id="current-password" type="password" name="currpassword" 
                  required data-msg="Por favor ingrese su contraseña actual" 
                  class="input-material form-control{{ $errors->has('currpassword') ? ' is-invalid' : '' }}" autocomplete="off" maxlength="100"
                  autocomplete="one-time-code">

              <label for="current-password" class="label-material active">Contraseña actual</label>
              @if ($errors->has('currpassword'))
              <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('currpassword') }}</strong>
              </span>
              @endif
            </div>


            <div class="form-group">
              <input id="reset-password" type="password" name="password" 
                  required data-msg="Por favor ingrese su nueva contraseña" 
                  class="input-material form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" autocomplete="off" maxlength="100" autocomplete="one-time-code">

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
                  class="input-material form-control{{ $errors->has('repassword') ? ' is-invalid' : '' }}" autocomplete="off" maxlength="100"autocomplete="one-time-code">

              <label for="reset-repassword" class="label-material active">Repetir contraseña</label>
              @if ($errors->has('repassword'))
              <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('repassword') }}</strong>
              </span>
              @endif
            </div>

            <div class="form-group text-center mb-0">
              <button type="submit" class="btn btn-primary">
                Actualizar
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>

@endsection
