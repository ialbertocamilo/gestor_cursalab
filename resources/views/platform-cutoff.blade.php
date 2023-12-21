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
  
  {{-- platform-suspended.png --}}
  <div class="login-page d-flex justify-content-center align-items-center">
    <div class="content_login_form mr-0">
      <div class="form-holder shadow-md px-5 py-5">
        <div class="--mb-5 text-center">
          <div class="--d-flex align-items-center mb-3">
            <img src="/img/platform-suspended-image.svg" width="60">
            <h3 class="mb-0 text-primary text-bold">¡Plataforma suspendida!</h3>
          </div>

          <p> 
            <span class="--text-primary">
              Comunícate con nuestro equipo para regularizar los pagos pendientes y reactivar los servicios de tu plataforma.
            </span>
          </p>

          <p class="text-bold">
              <a href="mailto:finanzas@cursalab.io">finanzas@cursalab.io</a>
          </p>
   {{--        

            <ul>
              <li>Mínimo 8 caracteres, debe incluir al menos una letra, un número y un caracter especial.</li>
              <li>No debe incluir ningún dato personal (Ej: correo, doc. de identidad, nombres o apellidos).</li>
            </ul> --}}

        </div>

      </div>
    </div>
  </div>

@endsection
