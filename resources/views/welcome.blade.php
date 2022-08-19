@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')

    @php
        $workspace = session('workspace');
        $workspace_logo = is_array($workspace) ? $workspace['logo'] : "img/logo_cursalab.png";
    @endphp
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
        <h2>Bienvenido(a) a </h2>
        <p style="font-size:large;">Configura tu contenido utilizando el menú lateral.</p>
        <div class="mt-5 mx-auto">
            <img src="{{ $workspace_logo }}" alt="" width="350">
        </div>
    </div>
@endsection
