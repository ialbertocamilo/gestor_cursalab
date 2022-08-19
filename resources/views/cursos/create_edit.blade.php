@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            if (request()->segment(1) == 'escuelas') {
                $id_curso = request()->segment(5) ?? null;
                $id_escuela = request()->segment(2);
            } else {
                $id_curso = request()->segment(3) ?? null;
                $id_escuela = null;
            }
        @endphp
        <curso-form-page modulo_id="{{ $id_escuela }}" categoria_id="{{ $id_escuela }}"
            curso_id="{{ $id_curso }}" />
    </v-app>
@endsection
