@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')
    <div style="
            height: 100vh;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center">
        <div class="mb-5 mx-auto">
            <img src="img/logo_cursalab.png" alt="..." class="img-fluid" width="230">
        </div>
        <h1>Bienvenido(a)</h1>
        <h2>a WeConnect 2.0</h2>
        <p>Empieza a configurar tu contenido utilizando el menú de la izquierda.</p>
    </div>
@endsection
