@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')
    <v-app>
        <checklist-activities-layout />
    </v-app>
@endsection
