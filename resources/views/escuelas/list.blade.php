@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        @php
            $modulo = \App\Models\Abconfig::find(request()->segment(2));
        @endphp
        <escuela-layout
            modulo_id="{{ request()->segment(2) }}"
            modulo_name="{{ $modulo->etapa ?? ''  }}"
        />
    </v-app>
@endsection
