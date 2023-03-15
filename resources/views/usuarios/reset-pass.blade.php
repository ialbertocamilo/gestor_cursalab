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
    background-color: transparent; 
    background-image: none; 
    background-position: bottom left;
    background-repeat: no-repeat;
  }

  .alert-box-fixed-center {
    z-index: 1000;
    position: fixed;
    position: absolute;
    top: 5%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

</style>

@endsection

@section('content')
  
  @include('layouts.user-header')
  
  @if (session('info'))
    <div class="alert-box-fixed-center" style="width: 550px;">
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
      <div class="form-holder shadow-md px-5">
        <div class="mb-5">
          <div class="d-flex align-items-center mb-3">
            <a class="mr-2 text-muted" href="/welcome">
             <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" style="fill: #505050ed;transform: scaleY(-1);msFilter:progid:DXImageTransform.Microsoft.BasicImage(rotation=2, mirror=1);"><path d="M12.707 17.293 8.414 13H18v-2H8.414l4.293-4.293-1.414-1.414L4.586 12l6.707 6.707z"></path></svg>
            </a>
            <h3 class="mb-0">Cambiar contraseña</h3>
          </div>

          <p> 
            <span class="text-primary">
              {{ auth()->user()->fullname }}
            </span>, elige una contraseña segura y no la utilices en otras cuentas ni la compartas con nadie.
          </p>

        {{--   <p>
            Seguridad de la contraseña:
            Utiliza al menos <b>8</b> caracteres, varia entre <b>mayúsculas</b> y <b>minúsculas</b>, caracteres especiales <b>(#&¡!$)</b> y evita usar un nombre demasiado obvio, como el de tu mascota o un familiar.
          </p> --}}

          {{-- <p> --}}
            <ul>
              <li>Mínimo 8 caracteres, debe incluir al menos una letra, un número y un caracter especial.</li>
              {{-- <li>No debe incluir caracteres consecutivos o repetidos (Ej: aaaa,1234,abcd).</li> --}}
              <li>No debe incluir ningún dato personal (Ej: correo, doc. de identidad, nombres o apellidos).</li>
            </ul>
          {{-- </p> --}}

        </div>

        <div class="form mt-2 p-0">
          <form method="POST" class="form-validate" autocomplete="off" action="{{ route('usuarios.user_password_reset') }}">
            @csrf

            <div class="form-group">
              <input id="current-password" type="password" name="currpassword" 
                  required data-msg="Por favor ingrese su contraseña actual" 
                  class="input-material form-control{{ $errors->has('currpassword') ? ' is-invalid' : '' }} no-icon one " autocomplete="off" maxlength="100"
                  autocomplete="one-time-code" autofocus>
              <div class="one toggle-eye text-muted" 
                onclick="toggleEyeInputId(this, 'current-password')">
                <span class="far fa-eye fa-lg"></span>
              </div>

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
                  class="input-material form-control{{ $errors->has('password') ? ' is-invalid' : '' }} no-icon two" autocomplete="off" maxlength="100" autocomplete="one-time-code">
              <div class="two toggle-eye text-muted" 
                 onclick="toggleEyeInputId(this, 'reset-password')">
                <span class="far fa-eye fa-lg"></span>
              </div>

              <label for="reset-password" class="label-material">Nueva contraseña</label>
              @if ($errors->has('password'))
              <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('password') }}</strong>
              </span>
              @endif
            </div>

            <div class="form-group">
              <input id="reset-repassword" type="password" name="repassword" 
                  required data-msg="Por favor repita su nueva contraseña" 
                  class="input-material form-control{{ $errors->has('repassword') ? ' is-invalid' : '' }} no-icon three" autocomplete="off" maxlength="100"autocomplete="one-time-code">
              <div class="three toggle-eye text-muted" 
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

            <div class="form-group text-center mb-0">
              <button type="submit" class="btn btn-primary">
                Actualizar contraseña
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>

@endsection
