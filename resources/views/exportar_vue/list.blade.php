@extends('layouts.appback')

@section('content')

  @include('layouts.user-header')

  <v-app>
    <reportes-{{ $layout }}-layout></reportes-{{ $layout }}-layout>
  </v-app>

@endsection