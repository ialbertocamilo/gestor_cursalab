@extends('layouts.appback')

@section('content')
    @php
    // $modulo = \App\Models\Abconfig::find(request()->segment(2));
    $escuela = \App\Models\School::find(request()->segment(2));
    $curso = \App\Models\Course::find(request()->segment(4));
    $tema = \App\Models\Topic::find(request()->segment(6));

    $taxonomy = \App\Models\Taxonomy::find($tema->type_evaluation_id);
    $evaluationTypeCode = $taxonomy->code ?? '';

    // dd($data);
    @endphp
    <v-app>
        @include('layouts.user-header')
        <tema-preguntas-layout
            modulo_id="{{ request()->segment(2) }}" modulo_name="{{ $escuela->name ?? '' }}"
            categoria_id="{{ request()->segment(2) }}" categoria_name="{{ $escuela->name ?? '' }}"
            curso_id="{{ request()->segment(4) }}" curso_name="{{ $curso->name ?? '' }}"
            tema_id="{{ request()->segment(6) }}" tema_name="{{ $tema->name ?? '' }}"
            evaluable="{{ $tema->type_evaluation_id }}"
            status="{{ $status }}"
            missing_score="{{ $data['score_missing'] ?? 0 }}"
            evaluation_type="{{ $evaluationTypeCode }}"
            evaluation_data_sum="{{ $data['sum'] ?? 0 }}"
            evaluation_data_sum_required="{{ $data['sum_required'] ?? 0 }}"
            evaluation_data_sum_not_required="{{ $data['sum_not_required'] ?? 0 }}"

            >
        </tema-preguntas-layout>
    </v-app>
@endsection
