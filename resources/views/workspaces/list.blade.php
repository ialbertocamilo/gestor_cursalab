@extends('layouts.appback', ['fullScreen' => true])

@section('morecss')

@endsection

@section('content')

    <script id="header-template" type="text/xhtml">
        @include('layouts.user-header')
    </script>
    <v-app>
        <workspaces-list-layout></workspaces-list-layout>
        {{-- <workspaces-list-layout></workspaces-list-layout> --}}
    </v-app>

@endsection
