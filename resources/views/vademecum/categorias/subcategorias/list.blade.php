@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')
    <v-app>
        <vademecum-subcategoria-layout categoria_id="{{ request()->segment(3) }}" />
    </v-app>
@endsection
