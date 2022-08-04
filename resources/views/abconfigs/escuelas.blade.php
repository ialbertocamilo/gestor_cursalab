@extends('layouts.appback')

@section('content')
    <v-app>
        @php
            $worskpace = session('workspace');
            $workspace_id = is_array($worskpace) ? $worskpace['id'] : null;
        @endphp
        <escuela-layout workspace_id="{{ $workspace_id }}" />
    </v-app>
@endsection
