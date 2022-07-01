@extends('layouts.appback')

@section('content')
  <header class="page-header">
    <div class="container-fluid">
            <div class="d-flex flex-row align-items-center">
            <div class="pr-4">
                <h2 class="no-margin-bottom">Encuestas Respuestas</h2>
            </div>
            <div>
                @can('encuestas_respuestas.create')
                    <a href="{{ route('encuestas_respuestas.create') }}" class="btn bg-green float-right"><i class="fa fa-plus"></i> Crear</a>
                @endcan
            </div>
            <div class="ml-auto">
                <form id="searchForm" action="#" role="search" >
                  <div class="input-group">
                  <input type="search" class="form-control" placeholder="Palabra clave" aria-label="Palabra clave" aria-describedby="basic-addon2">
                  <div class="input-group-append">
                    <button class="btn btn-secondary" type="button"><i class="fa fa-search"></i></button>
                  </div>
                </div>
                </form>
            </div>
        </div>
    </div>
  </header>

  <section class="forms section">   
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header d-flex align-items-center">
              <h3 class="h4">Editar Encuesta Respuesta</h3>
              <button class="btn btn-light ml-auto" onclick="history.back()"><i class="fa fa-arrow-left"></i></button>
            </div>
            <div class="card-body">
              {!! Form::model($encuestas_respuesta, ['route' => ['encuestas_respuestas.update', $encuestas_respuesta->id], 'method' => 'PUT']) !!}
                
                @include('encuestas_respuestas.partials.form')

              {!! Form::close() !!}

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
