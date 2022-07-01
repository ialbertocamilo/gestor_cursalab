@extends('layouts.appback')

@section('content')
  <header class="page-header">
    <div class="container-fluid">
      <div class="d-flex flex-row align-items-center">
        <div class="pr-4">
            <h2 class="no-margin-bottom">Convalidaciones</h2>
        </div>
      </div>
    </div>
  </header>

  <v-app>
    <section style="padding: 20px 0 0 20px;" >   
        <convalidaciones />
    </section>
  </v-app>

@endsection
