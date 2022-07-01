@extends('layouts.appback')

@section('content')
@include('layouts.user-header')
<section class="section-list">
  <header class="page-header">
    <div class="breadcrumb-holder container-fluid">
      <ul class="breadcrumb ">
        <li class="breadcrumb-item">Módulo: <br><a href="{{ route('abconfigs.categorias', $abconfig->id) }}">{{ $abconfig->etapa }}</a></li>
        <li class="breadcrumb-item active"><br><span>Escuelas</span></li>
      </ul>
    </div>
  </header>

  <div class="row mt-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center">
          <h5>Crear Escuela</h5>
        </div>
        <div class="card-body">
          {!! Form::open(['route' => ['categorias.store', $abconfig->id], 'enctype' => 'multipart/form-data']) !!}

          @include('categorias.partials.form')

          {!! Form::close() !!}

        </div>
      </div>
    </div>
  </div>
</section>
@endsection