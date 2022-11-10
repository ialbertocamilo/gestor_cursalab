@extends('layouts.appback')
@section('content')
    @include('layouts.user-header')
    <v-app>
        <usuario-layout :workspace_id="{{ get_current_workspace()->id ?? 0}}"/>
    </v-app>
@endsection