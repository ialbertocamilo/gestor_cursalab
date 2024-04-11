@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $workspace = session('workspace');
            $workspace = $workspace ? $workspace->toArray() : $workspace;
            $workspace_id = is_array($workspace) ? $workspace['id'] : null;

            $diploma = $diploma ?? NULL;

            $platform = session('platform');
            $btn_process = ($platform && $platform == 'induccion') ? 'true' : 'false';
            $btn_course = !($platform && $platform == 'induccion') ? 'true' : 'false';
        @endphp
        <diploma-form-page modulo_id="{{ $workspace_id }}"
                            diploma_id="{{ $diploma }}"
                            btn_process="{{ $btn_process }}"
                            btn_course="{{ $btn_course }}"
        />
    </v-app>
@endsection
