@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        @php
            $worskpace = session('workspace');
            $workspace_id = is_array($worskpace) ? $worskpace['id'] : null;
            $workspace_name = is_array($worskpace) ? $worskpace['name'] : '';
        @endphp
        <escuela-layout workspace_id="{{ $workspace_id }}" workspace_name="{{ $workspace_name ?? '' }}" />
    </v-app>
@endsection
