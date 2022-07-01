@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        <tema-layout
            modulo_id="{{ request()->segment(2) }}"
            categoria_id="{{ request()->segment(4) }}"
            curso_id="{{ request()->segment(6) }}"
        />
    </v-app>
@endsection
