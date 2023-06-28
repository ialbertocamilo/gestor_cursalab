@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $speaker_id = request()->segment(3) ?? null;
        @endphp

        <speaker-form-page ref="SpeakerFormPage" speaker_id="{{ $speaker_id }}"/>
    </v-app>
@endsection
