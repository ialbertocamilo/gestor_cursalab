@extends('layouts.appback')

@section('content')
    <v-app>
        @include('layouts.user-header')
        @php
            $modulo = \App\Models\Abconfig::find(request()->segment(2));
            $escuela = \App\Models\Categoria::find(request()->segment(4));
            $curso = \App\Models\Curso::find(request()->segment(6));
        @endphp
        <tema-layout
            modulo_id="{{ request()->segment(2) }}"
            modulo_name="{{ $modulo->etapa ?? ''  }}"
            categoria_id="{{ request()->segment(4) }}"
            categoria_name="{{ $escuela->nombre ?? '' }}"
            curso_id="{{ request()->segment(6) }}"
            curso_name="{{ $curso->nombre ?? '' }}"
        />
    </v-app>
@endsection
