@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $workspace = session('workspace');
            $workspace = $workspace ? $workspace->toArray() : $workspace;
            $workspace_id = is_array($workspace) ? $workspace['id'] : null;

            $votacion = $votacion ?? NULL;
        @endphp
        <votacion-form-page modulo_id="{{ $workspace_id }}" campana_id="{{ $votacion }}"/>
    </v-app>
@endsection
