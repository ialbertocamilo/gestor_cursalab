@extends('layouts.appback')

@section('content')
    @php
    // $modulo = \App\Models\Abconfig::find(request()->segment(2));
    $escuela = \App\Models\School::find(request()->segment(2));
    $curso = \App\Models\Course::find(request()->segment(4));
    $tema = \App\Models\Topic::find(request()->segment(6));
    @endphp
    <v-app>
        @include('layouts.user-header')
        <tema-preguntas-layout modulo_id="{{ request()->segment(2) }}" modulo_name="{{ $escuela->name ?? '' }}"
            categoria_id="{{ request()->segment(2) }}" categoria_name="{{ $escuela->name ?? '' }}"
            curso_id="{{ request()->segment(4) }}" curso_name="{{ $curso->name ?? '' }}"
            tema_id="{{ request()->segment(6) }}" tema_name="{{ $tema->name ?? '' }}"
            evaluable="{{ $tema->type_evaluation_id }}" />
    </v-app>
@endsection
