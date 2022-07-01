@extends('layouts.appback')
@section('content')
@include('layouts.user-header')
<section class="section-list">
    <header class="page-header">
        <div class="container-fluid">
            <div class="d-flex flex-row align-items-center justify-content-between">
                <h5 class="no-margin-bottom">Lista de Incidencias de Usuarios</h5>
            </div>
        </div>
    </header>

    <v-app>
        <usuario-ayuda />
    </v-app>
</section>
@endsection