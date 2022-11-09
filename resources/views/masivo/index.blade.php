@extends('layouts.appback')
@section('content')
    <v-app>
        @include('layouts.user-header')
        <subida-masiva-layout user_id={{\Auth::user()->id}} />
    </v-app>
@endsection
