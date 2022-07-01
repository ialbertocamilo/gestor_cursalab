@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')
    <v-app>
        <entrenadores-layout :roles="{{auth()->user()->roles}}"/>
    </v-app>
@endsection
