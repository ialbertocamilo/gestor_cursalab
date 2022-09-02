@extends('layouts.appback', ['fullScreen' => true])

@section('morecss')

@endsection

@section('content')
    @include('layouts.user-header')
    <v-app>
        <workspaces-list-layout></workspaces-list-layout>
    </v-app>

@endsection
