@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        <modulo-layout
            modulo_id="{{ request()->segment(2) }}"
        />
    </v-app>
@endsection
