@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        <encuesta-layout/>
    </v-app>
@endsection
