@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        @php
            $workspace = session('workspace');
            $workspace_id = is_array($workspace) ? $workspace['id'] : null;
            $workspace_name = is_array($workspace) ? $workspace['name'] : '';
        @endphp
        <escuela-layout workspace_id="{{ $workspace_id }}" workspace_name="{{ $workspace_name ?? '' }}" />
    </v-app>
@endsection
