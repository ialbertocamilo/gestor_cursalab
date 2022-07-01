@extends('layouts.appback')

@section('content')
  @include('layouts.user-header')
  <header class="page-header">
    <div class="container-fluid">
            <div class="d-flex flex-row align-items-center">
        </div>
    </div>
  </header>
  
  <v-app>
    <section style="padding: 20px 0 0 20px;" >   
        <boticas-layout/>
    </section>
  </v-app>
@endsection
