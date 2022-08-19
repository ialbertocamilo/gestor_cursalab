@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')
    <div style="
            height: 100vh;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-flow: column;">
        <!-- <div class="mb-5 mx-auto">
            <img src="img/logo_cursalab.png" alt="..." class="img-fluid" width="230">
        </div> -->
        <h2>Bienvenido(a) a WeConnect 2.0</h2>
        <p style="font-size:large;">Configura tu contenido utilizando el menú de la izquierda.</p>
        <div class="mt-5 mx-auto">
            <img src="img/welcome_gestor.svg" alt="" width="350">
        </div>
    </div>
@endsection
