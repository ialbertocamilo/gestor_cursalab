@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        @php

            $param2 = request()->segment(2);
            $param4 = request()->segment(4);

            $escuela_name = null;
            $curso_name = null;
            $id_escuela = null;
            $id_curso = $param2;
            $ruta = '';
            if (!is_null($param2) && !is_null($param4)) {
                $escuela = \App\Models\School::find($param2);
                $curso = \App\Models\Course::find($param4);

                $id_escuela = $param2;
                $id_curso = $curso->id;
                $escuela_name = $escuela->name ?? '';
                $curso_name = $curso->name ?? '';
                $ruta = 'escuelas/' . $id_escuela . '/';
            } else {
                $curso = \App\Models\Course::find($param2);
                $curso_name = $curso->name ?? '';
            }
        @endphp
        <tema-layout school_id="{{ $id_escuela }}" school_name="{{ $escuela_name }}" course_id="{{ $id_curso }}"
            course_name="{{ $curso_name }}" ruta="{{ $ruta }}" />
    </v-app>
@endsection
