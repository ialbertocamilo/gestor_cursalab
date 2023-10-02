@extends('layouts.appback')
@section('content')
    <v-app>
        @include('layouts.user-header') 
        <user-layout :workspace_id="{{ get_current_workspace()->id ?? 0}}"/>
    </v-app>
@endsection