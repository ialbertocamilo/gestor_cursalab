@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $speaker_id = request()->segment(3) ?? null;
            $mode_assign = request()->query('mode') ? true : false;
        @endphp

        <speaker-form-page ref="SpeakerFormPage" speaker_id="{{ $speaker_id }}" mode_assign="{{ $mode_assign }}"/>
    </v-app>
@endsection
