@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        <benefit-form-page ref="BenefitFormPage"/>
    </v-app>
@endsection
