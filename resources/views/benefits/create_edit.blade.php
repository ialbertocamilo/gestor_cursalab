@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $benefit_id = null;
        @endphp

        <benefit-form-page ref="BenefitFormPage" benefit_id="{{ $benefit_id }}"/>
    </v-app>
@endsection
