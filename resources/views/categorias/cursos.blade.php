<?php

use App\Http\Controllers\CursosController;
?>

@extends('layouts.appback')

@section('content')
@include('layouts.user-header')
<section class="section-list">
  <header class="page-header">
    <div class="breadcrumb-holder container-fluid">
      <ul class="breadcrumb ">
        <li class="breadcrumb-item">Módulo: <br><a href="{{ route('abconfigs.categorias', $categoria->config->id) }}">{{ $categoria->config->etapa }}</a></li>
        <li class="breadcrumb-item"><span>Escuela:</span> <br><a href="{{ route('categorias.cursos', [$categoria->config->id, $categoria->id]) }}"><span>{{ $categoria->nombre }}</span></a></li>
        <li class="breadcrumb-item active"><span>Cursos</span></li>
      </ul>
    </div>

    @can('cursos.create')
    <div class="container-fluid">
      <a href="{{ route('cursos.create', $categoria->id) }}" class="btn bg-green"><i class="fa fa-plus"></i> Curso</a>
    </div>
    @endcan
  </header>

  <div class="no-padding-bottom mt-3">
    <div class="home_mod_tabs curso_tabs">
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link {{ (!app('request')->input('pid')) ? 'active' : '' }}" href="{{ url()->current() }}">Todos</a>
        </li>
        @foreach ($carreras as $carrera)
        <li class="nav-item">
          <a class="nav-link {{ (app('request')->input('pid') == $carrera->id) ? 'active' : '' }}" href="{{ url()->current().'?pid='.$carrera->id }}">{{ $carrera->nombre }}</a>
        </li>
        @endforeach
      </ul>
    </div>
  </div>

  <div class="client">
    @if (count($c_eliminados)>0)
    <div class="alert alert-warning mx-3 p-4" role="alert">
      Los siguientes cursos han sido eliminado y necesitan ser actualizados.
      <ol class="list-group list-group-numbered">
        @foreach ($c_eliminados as $item)
        <li style="margin-left: 3rem;">{{$item->extra_info}} - Este proceso terminará aproximadamente a las {{$item->parse_date}}</li>
        @endforeach
      </ol>
    </div>
    @endif
    <div class="row cate_cursos">

      @foreach($cursos as $curso)
      <?php
      $array = config('constantes.modalidad');
      ?>
      <div class="col-md-4 col-sm-12 col-xs-12">
        <div class=" d-flex">
          <div class="client card card1">
            <div id="curso_{{$curso->id}}" class="card-body text-center">
              <div class="card-close">
                @can('cursos.create')
                <a href="{{ route('cursos.edit', [$categoria->id, $curso->id] ) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a>
                @endcan
                @can('cursos.destroy')
                {!! Form::open(['route' => ['cursos.destroy', $categoria->id, $curso->id], 'method' => 'DELETE']) !!}
                <button class="btn btn-sm bg-red btndelete mt-1"><i class="far fa-trash-alt"></i></button>
                {!! Form::close() !!}
                @endcan
              </div>

              <div class="card-img">
                <img src="{{ Storage::disk('do_spaces')->url($curso->imagen) }}" alt="..." class="img-fluid">
              </div>

              <div class="client-title">
                <!-- <h3>{{ $curso->id }}</h3> -->
                <h3>{{ $curso->nombre }}</h3>
                <span>{{ ($curso->estado == 1) ? "Activo" : "Inactivo" }}</span>
                <span>Código: {{ $curso->codigo }}</span>
                <span>Requisito: {{ ($curso->requisito) ? $curso->requisito->nombre : 'NINGUNO' }}</span>
                <span>Evaluable: {{ $curso->c_evaluable }}</span>
                {{-- <span>libre: {{ ($curso->libre)? 'Sí' : 'No' }}</span> --}}
                <span>Orden: {{ $curso->orden }}</span>
              </div>

              <hr>
              <?php //print_r($curso->encuesta); 
              ?>

              @can('cursos.edit')
              <a href="{{ route('cursos.temas', [$categoria->id, $curso->id] ) }}" class="btn btn-secondary btn-bigicon"><i class="fas fa-bookmark"></i><br>Temas</a>

              @can('cursos.edit')
              @if( $curso->encuesta )
              <div class="btn btn-mini ml-1">
                <i class="fas fa-check-circle"></i>Encuesta<br>asociada<br>
                <a href="{{ route('curso_encuesta.edit', ['curso' => $curso->id, 'ce' => $curso->encuesta->id]) }}" class="btn btn-link btn-mini" title="Cambiar encuesta"><i class="fas fa-sync-alt"></i></a>
              </div>
              @else
              <a href="{{ route('curso_encuesta.create', $curso->id) }}" class="btn btn-link btn-mini ml-1"><i class="fa fa-plus"></i><br> Encuesta</a>
              @endif
              @endcan

              @endcan
            </div>
            <hr>
            @if (isset($curso->update_usuarios) && count($curso->update_usuarios)>0)
            <div class="alert alert-warning mx-3 p-2" role="alert">
              Este curso tiene ({{count($curso->update_usuarios)}}) actualizacion(es) pendiente. Este proceso terminará aproximadamente a las {{$curso->parse_date}}
            </div>
            @endif
            {{-- <div style="margin:10px;border: 1px solid gray; padding: 10px;max-height: 80px;overflow: auto;min-height: 80px">
              @foreach($curso->curricula as $curri)
                <p>{{$curri->carrera->nombre}} ({{$curri->ciclo->nombre}}) <i onclick="show_modal({{$curri->id}})" class="fas fa-eye ml-3"></i></p>
            @endforeach
          </div> --}}
        </div>
        <div class="client card card2">
          <div class="car_ciclos" style="overflow: auto;max-height: 325px;min-height: 325px;">
            @foreach($curso->curricula as $curri)
            @isset($curri->carrera->nombre)
            <div class="carre_ciclo">
              <span class="cc_tit">{{ $curri->carrera->nombre}} <i onclick="show_modal({{$curri->id}})" class="fas fa-eye ml-3"></i></span>
              <span class="cc_desc">{{ $curri->ciclo->nombre }}</span>
            </div>
            @endisset
            @endforeach
          </div>
        </div>
      </div>
      <!-- <div class="client card card2">
                          <div class="car_ciclos"> -->
      <?php
      //$curricula = CursosController::get_curricula_agrupada($curso->id); 
      // @foreach($curricula as $curri)
      //     <div class="carre_ciclo">
      //       <span class="cc_tit">{{ $carreras_arr[$curri['carrera_id']] }}</span> 
      //       @if($curri['ciclos'])
      //         @foreach($curri['ciclos'] as $curri2)
      //           <span class="cc_desc">{{ $ciclos_arr[$curri2] }}</span>
      //         @endforeach
      //       @endif
      //     </div>
      // @endforeach
      ?>
      <!-- </div>
                        </div> -->
    </div>
    @endforeach
    <div class="pt-2 float-right">
      <!-- $cursos->appends(request()->query())->links() -->
    </div>
  </div>
  </div>
  <!-- Modal -->
  <div class="modal" tabindex="-1" role="dialog" id="myModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="height: 700px;overflow: auto;">
        <div class="modal-header">
          <h5 class="modal-title">Grupos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <table class="table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
              </tr>
            </thead>
            <tbody id="gruposT">

            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
@section('js')
@parent
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
  function show_modal(id) {
    $.ajax({
      type: 'GET',
      url: '/getGruposXCurricula/' + id
    }).done(function(result) { //
      // it 
      result.forEach((item, index) => {
        const row = `<tr>
                    <td>${index +1}</td>
                    <td>${ item.criterio.valor }</td>
                </tr>`;
        $('#gruposT').append(row);
      });
      $('#myModal').modal('show');
    });
  }
</script>
@endsection