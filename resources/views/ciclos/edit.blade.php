@extends('layouts.appback')

@section('content')
<header class="page-header">
    <div class="breadcrumb-holder container-fluid">
      <ul class="breadcrumb ">
        <li class="breadcrumb-item">Inicio: <br><a href="{{ route('carreras.index') }}">Carreras</a></li>
        <li class="breadcrumb-item"><span>Carrera:</span> <br><a href="{{ route('carreras.ciclos', [$carrera->config->id, $carrera->id]) }}"><span>{{ $carrera->config->etapa }} -> {{ $carrera->nombre }}</span></a></li>
        <li class="breadcrumb-item active"><span>Ciclos</span></li>
      </ul>
    </div>
  </header>

  <section class="forms section">   
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header d-flex align-items-center">
              <h3 class="h4">Editar Ciclo</h3>
            </div>
            <div class="card-body">
              {!! Form::model($ciclo, ['route' => ['ciclos.update', $carrera->id, $ciclo->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) !!}
                
                @include('ciclos.partials.form')

              {!! Form::close() !!}

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
