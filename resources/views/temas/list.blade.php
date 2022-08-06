@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        @php
            // $modulo = \App\Models\Abconfig::find(request()->segment(2));
            $school = \App\Models\School::find(request()->segment(2));
            $curso = \App\Models\Course::find(request()->segment(4));
        @endphp
        <tema-layout school_id="{{ request()->segment(2) }}" school_name="{{ $school->name ?? '' }}"
            course_id="{{ request()->segment(4) }}" course_name="{{ $curso->name ?? '' }}" />
    </v-app>
@endsection
