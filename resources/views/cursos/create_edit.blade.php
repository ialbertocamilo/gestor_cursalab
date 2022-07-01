@extends('layouts.appback')

@section('content')
<v-app>
    @include('layouts.user-header')

    @php
        $curso = request()->segment(7) ?? null
    @endphp
    <curso-form-page
        modulo_id="{{request()->segment(2)}}"
        categoria_id="{{request()->segment(4)}}"
        curso_id="{{$curso}}"
    />
</v-app>
@endsection
