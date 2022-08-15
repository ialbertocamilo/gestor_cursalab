@extends('layouts.appback')

@section('content')
    <v-app>
        @php
            $workspace = session('workspace');
            $workspace_id = is_array($workspace) ? $workspace['id'] : null;
        @endphp
        <escuela-layout workspace_id="{{ $workspace_id }}" />
    </v-app>
@endsection
