@extends('layouts.appback')

@section('content')
    @php
        $modulo = \App\Models\Abconfig::find(request()->segment(2));
        $escuela = \App\Models\Categoria::find(request()->segment(4));
        $curso = \App\Models\Curso::find(request()->segment(6));
        $tema = \App\Models\Posteo::find(request()->segment(8));
    @endphp
    <v-app>
        @include('layouts.user-header')
        <tema-preguntas-layout
            modulo_id="{{ request()->segment(2) }}"
            modulo_name="{{ $modulo->etapa ?? ''  }}"
            categoria_id="{{ request()->segment(4) }}"
            categoria_name="{{ $escuela->nombre ?? '' }}"
            curso_id="{{ request()->segment(6) }}"
            curso_name="{{ $curso->nombre ?? '' }}"
            tema_id="{{ request()->segment(8) }}"
            tema_name="{{ $tema->nombre ?? '' }}"
            evaluable="{{$tema->tipo_ev}}"
        />
    </v-app>
@endsection
