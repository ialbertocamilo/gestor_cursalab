@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        @php
            $modulo = \App\Models\Abconfig::find(request()->segment(2));
            $escuela = \App\Models\Categoria::find(request()->segment(4));
        @endphp
        <curso-layout
            modulo_id="{{ request()->segment(2) }}"
            modulo_name="{{ $modulo->etapa ?? ''  }}"
            categoria_id="{{ request()->segment(4) }}"
            categoria_name="{{ $escuela->nombre ?? '' }}"
        />
    </v-app>
@endsection
