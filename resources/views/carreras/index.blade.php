@extends('layouts.appback')

@section('content')
  @include('layouts.user-header')
 <header class="page-header">
    <div class="breadcrumb-holder container-fluid">
      <ul class="breadcrumb ">
      <li class="breadcrumb-item">Inicio: <br><a href="{{ route('carreras.index') }}">Carreras</a></li>
      </ul>
    </div>

  </header>

  <section class="no-padding-bottom">
    <div class="container-fluid">
    </div>
  </section>
  
  <section class="client">
    <div class="container-fluid">
      <div class="row ciclos">
        @foreach($configs as $config)
          <div class="col-lg-6">
            <div class="articles card">
              
              <div class="card-header d-flex align-items-center">
                <h2 class="h3">{{ $config->etapa }}</h2>
                <div class="badge badge-rounded {{ ($config->estado == 1) ? 'bg-green' : 'bg-red' }}">{{ ($config->estado == 1) ? "Activo" : "Inactivo" }}</div>
              </div>

                @foreach($carreras as $carrera)
                    @if($carrera->config_id == $config->id)

                    <?php 
                        $params = [$config->id, $carrera->id];
                    ?>
                    <div class="item d-flex align-items-center justify-content-between">
                        <div class="d-flex" style="width: 40%">
                            <div class="image"><img src="{{ Storage::disk('do_spaces')->url($carrera->imagen ?? '') }}" alt="..." class=""></div>
                            <div class="text" >
                                <h3 class="h5">{{ $carrera->nombre }}</h3>
                                <small>{{ ($carrera->estado == 1) ? "Activo" : "Inactivo" }}</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <a href="{{ route('carreras.ciclos', $params ) }}" class="btn btn-secondary"><i class="fas fa-bookmark"></i> Ciclos</a> 
                        </div>

                        <div class="text carrera_btns">
                            @can('carreras.create')
                            <a href="{{ route('carreras.edit', $params ) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a> 
                            @endcan
                            @can('carreras.destroy')
                                {!! Form::open(['route' => ['carreras.destroy', $config->id, $carrera->id ], 'method' => 'DELETE']) !!}
                                    <button class="btn btn-sm bg-red btndelete mt-1"><i class="far fa-trash-alt"></i></button>
                                {!! Form::close() !!}
                            @endcan
                        </div>
                    </div>
                    @endif
                @endforeach

                <div class=" d-flex align-items-center justify-content-center item-addnew">
                    <a href="{{ route('carreras.create', $config->id) }}" class="btn btn-lg bg-light"><i class="fa fa-plus"></i></a>
                </div>

            </div>
          </div>
        @endforeach

      </div>

    </div>
  </section>

@endsection
