@extends('layouts.appback')

@section('content')
 <header class="page-header">
    <div class="breadcrumb-holder container-fluid">
      <ul class="breadcrumb ">
        <li class="breadcrumb-item">Inicio: <br><a href="{{ route('carreras.index') }}">Carreras</a></li>
        <li class="breadcrumb-item"><span>Carrera:</span> <br><a href="{{ route('carreras.ciclos', [$abconfig->id, $carrera->id]) }}"><span>{{ $carrera->config->etapa }}-> {{ $carrera->nombre }}</span></a></li>
        <li class="breadcrumb-item active"><span>Ciclos</span></li>
      </ul>
    </div>

    @can('carreras.create')
      <div class="container-fluid">
        <a href="{{ route('ciclos.create', $carrera->id) }}" class="btn bg-green"><i class="fa fa-plus"></i> Ciclo</a>
      </div>
    @endcan
  </header>

  <section class="no-padding-bottom">
    <div class="container-fluid">
    </div>
  </section>
  
  <section class="client">
    <div class="container-fluid">
      <div class="row ciclos">
        @foreach($ciclos as $ciclo)
          <?php 
            $params = [$carrera->id, $ciclo->id];
          ?>
          <div class="col-lg-6">
            <div class="articles card">
              <div class="card-close ciclo_btns">
                  @can('carreras.create')
                     <a href="{{ route('ciclos.edit', $params ) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a> 
                  @endcan

                  @can('carreras.destroy')
                      {!! Form::open(['route' => ['ciclos.destroy', $carrera->id, $ciclo->id ], 'method' => 'DELETE']) !!}
                          <button class="btn btn-sm bg-red btndelete ml-1"><i class="far fa-trash-alt"></i></button>
                      {!! Form::close() !!}
                  @endcan
              </div>
              
              <div class="card-header d-flex align-items-center">
                <h2 class="h3">{{ $ciclo->nombre }}</h2>
                <div class="badge badge-rounded {{ ($ciclo->estado == 1) ? 'bg-green' : 'bg-red' }}">{{ ($ciclo->estado == 1) ? "Activo" : "Inactivo" }}</div>
              </div>
              
              @foreach($cursos as $curso)
                @if($curso->ciclo_id == $ciclo->id)
                  <div class="item d-flex align-items-center justify-content-between">
                    <div class="d-flex" >
                      <div class="image"><img src="{{ asset($curso->imagen) }}" alt="..." class=""></div>
                      <div class="text" data-id="{{ $curso->id }}">
                        <span style="background: {{ $cate_color[$curso->categoria_id] }}">{{ $categorias[$curso->categoria_id] }}</span>
                        <h3 class="h5">{{ $curso->nombre }}</h3>
                        <small>{{ ($curso->estado == 1) ? "Activo" : "Inactivo" }} | {{ ($curso->c_evaluable == 'si') ? "Evaluable" : "No evaluable" }}</small>
                      </div>
                    </div>
                    
                    <!-- <div class="d-flex align-items-center">
                      <a href="{{ route('cursos.temas', [$ciclo->id, $curso->id] ) }}" class="btn btn-secondary"><i class="fas fa-bookmark"></i> Temas</a> 
                    </div> -->

                    <!-- <div class="text curso_btns">
                      @can('cursos.create')
                         <a href="{{ route('cursos.edit', [$ciclo->id, $curso->id] ) }}" class="btn btn-xs bg-orange"><i class="fas fa-edit"></i></a> 
                      @endcan
                      @can('cursos.destroy')
                          {!! Form::open(['route' => ['cursos.destroy', $carrera->id, $curso->id], 'method' => 'DELETE']) !!}
                              <button class="btn btn-xs bg-red btndelete mt-1"><i class="far fa-trash-alt"></i></button>
                          {!! Form::close() !!}
                      @endcan
                    </div> -->

                  </div>
                @endif
              @endforeach

                <!-- <div class=" d-flex align-items-center justify-content-center item-addnew">
                    <a href="{{ route('cursos.create', $ciclo->id) }}" class="btn btn-lg bg-light"><i class="fa fa-plus"></i></a>
                </div> -->

            </div>
          </div>
        @endforeach

      </div>

      <div class="pt-2 float-right">
        {{ $ciclos->render() }}
      </div>

    </div>
  </section>

@endsection
