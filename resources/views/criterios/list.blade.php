@extends('layouts.appback')

@section('content')
    <v-app>
        <criterio-layout tipo_criterio_id="{{ request()->segment(2) }}"/>
    </v-app>
@endsection
