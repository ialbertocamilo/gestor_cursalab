@extends('layouts.appback')

@section('content')
<v-app>
    @include('layouts.user-header')

    @php
        $categoria = request()->segment(5) ?? null
    @endphp
    <escuela-form-page
        modulo_id="{{request()->segment(2)}}"
        categoria_id="{{$categoria}}"
    />
</v-app>
@endsection
