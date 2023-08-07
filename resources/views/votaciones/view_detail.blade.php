@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $workspace = session('workspace');
            $workspace = $workspace ? $workspace->toArray() : $workspace;
            $workspace_id = is_array($workspace) ? $workspace['id'] : null;

            $campaign = $campaign ?? NULL;
        @endphp
        <votacion-detail-page modulo_id="{{ $workspace_id }}" campaign_id="{{ $campaign }}"/>
    </v-app>
@endsection
