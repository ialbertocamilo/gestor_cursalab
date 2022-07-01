@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')
    <v-app>
        <meetings-layout/>
        {{-- <aulas-virtuales-layout :usuario_id="{{Auth::user()->id}}"/> --}}
    </v-app>
@endsection
