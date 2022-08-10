@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $tema = request()->segment(7) ?? null;
        @endphp
        <tema-form-page school_id="{{ request()->segment(2) }}" course_id="{{ request()->segment(4) }}"
            topic_id="{{ $tema }}" />
    </v-app>
@endsection
