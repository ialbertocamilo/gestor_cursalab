@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $workspace = session('workspace');
            $workspace = $workspace ? $workspace->toArray() : $workspace;
            $workspace_id = is_array($workspace) ? $workspace['id'] : null;

            $diploma = $diploma ?? NULL;
        @endphp
        <diploma-form-page modulo_id="{{ $workspace_id }}" diploma_id="{{ $diploma }}"/>
    </v-app>
@endsection
