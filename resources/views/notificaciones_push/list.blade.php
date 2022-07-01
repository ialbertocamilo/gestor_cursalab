@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        <notificaciones-push-layout/>
    </v-app>
@endsection
