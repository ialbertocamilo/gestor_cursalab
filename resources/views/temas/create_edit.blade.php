@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $tema = request()->segment(7) ?? null;
        @endphp
        <tema-form-page modulo_id="{{ request()->segment(2) }}" categoria_id="{{ request()->segment(2) }}"
            curso_id="{{ request()->segment(4) }}" tema_id="{{ $tema }}" />
    </v-app>
@endsection
