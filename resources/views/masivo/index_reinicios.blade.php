@extends('layouts.appback')

@section('content')

{{-- <header class="page-header">
  <div class="container-fluid">
          <div class="d-flex flex-row align-items-center">
          <div class="pr-4">
              <h2 class="no-margin-bottom">Reinicios masivos</h2>
          </div>
      </div>
  </div>
</header> --}}
@include('layouts.user-header')
<v-app>
    <reinicios-masivos :admin="{{Auth::user()}}"/>
</v-app>
@endsection