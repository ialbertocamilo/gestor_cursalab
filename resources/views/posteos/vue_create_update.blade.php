@extends('layouts.appback')

@section('content')
@include('layouts.user-header')
<evaluacion-form :pregunta_id="{{isset($pregunta) &&$pregunta->id ? $pregunta->id : 0}}" post_id="{{$posteo->id}}" curso_id="{{$posteo->curso_id}}" evaluable="{{$posteo->tipo_ev}}"></evaluacion-form>
@endsection