@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        @php
            // $modulo = \App\Models\Workspace::find(request()->segment(2));
            $escuela = \App\Models\School::find(request()->segment(2));
        @endphp
        <curso-layout escuela_id="{{ request()->segment(2) }}" escuela_name="{{ $escuela->name ?? '' }}" />
    </v-app>
@endsection
