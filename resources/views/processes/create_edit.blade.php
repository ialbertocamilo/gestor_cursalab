@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $benefit_id = request()->segment(3) ?? null;
        @endphp

        <benefit-form-page ref="BenefitFormPage" benefit_id="{{ $benefit_id }}"/>
    </v-app>
@endsection
