@extends('layouts.appback')

@section('content')
@include('layouts.user-header')
<section class="section-list">
  <header class="page-header">
    <div class="breadcrumb-holder container-fluid">
      <ul class="breadcrumb ">
        <li class="breadcrumb-item">Módulo: <br><a href="{{ route('abconfigs.categorias', $abconfig->id) }}">{{ $abconfig->etapa }}</a></li>
        <li class="breadcrumb-item active"><span>Escuela:</span><br><strong>{{ $categoria->nombre }}</strong></li>
      </ul>
    </div>
  </header>

  <div class="row mt-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center">
          <h5>Editar</h5>
        </div>
        <div class="card-body">
          {!! Form::model($categoria, ['route' => ['categorias.update', $abconfig->id, $categoria->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) !!}

          @include('categorias.partials.form')

          {!! Form::close() !!}

        </div>
      </div>
    </div>
  </div>
</section>
@endsection