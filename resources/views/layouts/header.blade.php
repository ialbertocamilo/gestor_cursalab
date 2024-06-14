<?php
  $config = \App\Models\Ambiente::first();
  $title = $config->titulo ?? 'Gestor';
  $favicon = $config->icono ? get_media_url($config->icono) : asset('img/favicon.png');
?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cursalab | {{ $title }}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    {{-- <meta name="robots" content="all,follow"> --}}
    <meta name="robots" content="noindex">
    <meta name="BUCKET_BASE_URL" content="{{ App\Services\FileService::generateUrl('/') }}">
    <meta name="REPORTS_BASE_URL" content="{{ env('REPORTS_BASE_URL') }}">
    <meta name="JARVIS_BASE_URL" content="{{ env('JARVIS_BASE_URL') }}">
    <meta name="EDITOR" content="{{ base64_encode(env('EDITOR_API_KEY')) }}">

    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <!-- Font Awesome CSS-->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">

{{--    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">--}}
{{--    <link href="{{asset('css/materialdesignicons.mins.css')}}" rel="stylesheet">--}}

{{--      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">--}}
      <link href="{{asset('css/fontawesome/css/all.min.css')}}" rel="stylesheet">

      <!-- Fontastic Custom icon font-->
    <link rel="stylesheet" href="{{ asset('css/fontastic.css') }}">
    <!-- Google fonts  -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,700" rel="stylesheet">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="{{ asset('css/style.default.css?v=2.3420' . date('Y-W') ) }}" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="{{ asset('css/custom.css?v=2.3420-'.date('Y-W')) }}">
    <link rel="stylesheet" href="{{ asset('css/app.css?v=2.3420-'.date('Y-W')) }}">
    <!-- Favicon-->
    <link rel="shortcut icon" href="{{ $favicon }}">

    @if(config('app.env') == 'production')

      <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>

    @endif

    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
    @yield('morecss')

    @include('layouts.gtag-manager-head')
    {{-- include('layouts.gtag') --}}

    @stack('css')
  </head>
  <body>
    @include('layouts.gtag-manager-body')
