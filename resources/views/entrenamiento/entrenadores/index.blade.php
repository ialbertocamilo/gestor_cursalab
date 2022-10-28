@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')
    @php
        $is_super_user = (bool) auth()
            ->user()
            ->isAn('super-user');
        $is_super_user = $is_super_user ? 1 : 0;
    @endphp
    <v-app>
        <entrenadores-layout :roles="{{ $is_super_user }}" />
    </v-app>
@endsection
