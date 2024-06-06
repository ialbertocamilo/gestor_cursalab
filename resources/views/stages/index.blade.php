@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')

    @php
        $process_id = request()->segment(2) ?? null;
        $process_id = $process_id ? intval($process_id) : null;
        $process_name = $process_id ? \App\Models\Process::where('id', $process_id)->first('title')?->title : null;
        $show_pasantia = $process_id ? \App\Models\Process::where('id', $process_id)->first('corporate_process')?->corporate_process : null;
        $show_pasantia = $show_pasantia ? 'true' : 'false';
    @endphp

    <v-app>
        <stages-layout :process_id="{{ $process_id }}" :process_name="'{{ $process_name }}'" :show_pasantia="{{ $show_pasantia }}" />
    </v-app>
@endsection
