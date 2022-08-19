@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $id_curso = request()->segment(5) ?? null;
            $id_escuela = request()->segment(2);
            if (is_null($id_curso)) {
                $id_escuela = null;
                $id_curso = request()->segment(3) ?? null;
            }
        @endphp
        <curso-form-page modulo_id="{{ $id_escuela }}" categoria_id="{{ $id_escuela }}"
            curso_id="{{ $id_curso }}" />
    </v-app>
@endsection
