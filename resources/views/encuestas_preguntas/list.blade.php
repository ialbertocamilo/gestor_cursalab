@extends('layouts.appback')

@section('content')
    <v-app>
        <encuesta-pregunta-layout encuesta_id="{{ request()->segment(2) }}"/>
    </v-app>
@endsection
