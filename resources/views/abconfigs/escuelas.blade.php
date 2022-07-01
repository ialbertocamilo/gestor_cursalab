@extends('layouts.appback')

@section('content')
    <v-app>
        <escuela-layout modulo_id="{{ request()->segment(2) }}"/>
    </v-app>
@endsection
