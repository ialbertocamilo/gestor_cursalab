@extends('layouts.appback')

@section('content')
{{--  @include('layouts.user-header')--}}
{{--  <section class="section-list">--}}
{{--    <header class="page-header">--}}
{{--      <div class="container-fluid">--}}
{{--        <div class="d-flex flex-row align-items-center justify-content-between">--}}
{{--          <h5 class="no-margin-bottom">Reportes de conferencias</h5>--}}
{{--        </div>--}}
{{--      </div>--}}
{{--    </header>--}}
{{--  </section>--}}
{{--  <div class="section">--}}
{{--        <reportes-conf-layout/>--}}
{{--  </div>--}}
  <v-app>
      @include('layouts.user-header')
      <reportes-conf-layout/>
  </v-app>
@endsection
