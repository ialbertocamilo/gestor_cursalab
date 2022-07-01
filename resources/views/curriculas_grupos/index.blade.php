@extends('layouts.appback')


@section('content')
@include('layouts.user-header')
<section class="section-list">
  <header class="page-header">
    <div class="container-fluid">
      <div class="d-flex flex-row align-items-center justify-content-between">
        <h5 class="no-margin-bottom">Segmentación</h5>
        <div>
          <a class="mx-auto" href="/exportar/curricula_excel" target="_blank" style="text-decoration: none">
            <v-btn>Descargar segmentación</v-btn>
          </a>
        </div>
      </div>
    </div>
  </header>

  <div class="mt-3">
    <v-app>
      <curricula-grupos :user_data="{{Auth::user()}}"></curricula-grupos>
    </v-app>
  </div>
</section>
@endsection