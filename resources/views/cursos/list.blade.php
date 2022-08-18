@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        @php
            $id_school = request()->segment(2);
            $escuela_name = null;
            if (!is_null($id_school)) {
                $escuela = \App\Models\School::find($id_school);
                $escuela_name = $escuela->name ?? '';
            }
        @endphp
        <curso-layout escuela_id="{{ $id_school }}" escuela_name="{{ $escuela_name }}" />
    </v-app>
@endsection
