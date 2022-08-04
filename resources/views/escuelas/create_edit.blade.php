@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $worskpace = session('workspace');
            $workspace_id = is_array($worskpace) ? $worskpace['id'] : null;

            $escuela = request()->segment(3) ?? null;
        @endphp
        <escuela-form-page modulo_id="{{ $workspace_id }}" categoria_id="{{ $escuela }}" />
    </v-app>
@endsection
