@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')

    @php
        $process_id = request()->segment(2) ?? null;
        $process_id = $process_id ? intval($process_id) : null;
        $process_name = $process_id ? \App\Models\Process::where('id', $process_id)->first('title')?->title : null;
    @endphp

    <v-app>
        <processes-assistants-layout :process_id="{{ $process_id }}" :process_name="'{{ $process_name }}'" />
    </v-app>
@endsection
