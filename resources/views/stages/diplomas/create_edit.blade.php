@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')

        @php
            $workspace = session('workspace');
            $workspace = $workspace ? $workspace->toArray() : $workspace;
            $workspace_id = is_array($workspace) ? $workspace['id'] : null;

            $diploma = $diploma ?? NULL;
            $model_id = $stage ?? NULL;
            $model_type = Stage::class;
            $redirect = route('stages.index', [$process]);

        @endphp
        <diploma-form-page modulo_id="{{ $workspace_id }}"
                            diploma_id="{{ $diploma }}"
                            model_id="{{ $model_id }}"
                            model_type="{{ $model_type }}"
                            redirect="{{ $redirect }}"
                            btn_process="true"
                            btn_course="false"
        />
    </v-app>
@endsection
