@extends('layouts.appback')
@section('content')
    <v-app>
        @include('layouts.user-header')
        <upload-topic-grades-layout user_id="{{\Auth::user()->id}}" />
    </v-app>
@endsection
