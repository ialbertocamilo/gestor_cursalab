@extends('layouts.appback')

@section('content')
@include('layouts.user-header')
<v-app>
    <pregunta-layout zzzz_id="{{ request()->segment(2) }}" />
</v-app>
@endsection