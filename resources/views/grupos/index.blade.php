@extends('layouts.appback')

@section('content')

@include('layouts.user-header')
<section class="section-list">
  <header class="page-header">
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <h5 class="no-margin-bottom">Código de Matrícula</h5>
      {{--     <div class="ml-2">
            @can('grupos.create')
            <a href="{{ route('grupos.create') }}" class="btn bg-green float-right"><i class="fa fa-plus"></i> Crear</a>
            @endcan
          </div> --}}
        </div>
        <div class="d-flex align-items-center">
          <div class="ml-auto">
            <form id="searchForm" action="#" role="search">
              <div class="input-group">
                <input type="search" name='q' class="form-control" placeholder="Palabra clave" aria-label="Palabra clave" aria-describedby="basic-addon2">
                <div class="input-group-append">
                  <button class="btn btn-secondary" type="submit"><i class="fa fa-search"></i></button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="row">
    <div class="col-12">

      <table class="table table-hover ">
        <thead class="bg-dark">
          <tr>
            <!-- <th>#</th> -->
            <th>Nombre</th>
            <!-- <th>Fecha inicio</th> -->
            <!-- <th>Fecha fin</th> -->
            <th>Estado</th>
            <th colspan="3">&nbsp;</th>
          </tr>
        </thead>
        <tbody>
          @foreach($grupos as $grupo)
          <tr>
            <!-- <th scope="row">{{ $grupo->id }}</th> -->
            <td>{{ $grupo->nombre }}</td>
            <!-- <td>{{ $grupo->fecha_inicio }}</td> -->
            <!-- <td>{{ $grupo->fecha_fin }}</td> -->
            <td>{{ ($grupo->estado == 1) ? "Activo" : "Inactivo" }}</td>

            <td width="30px">
              @can('grupos.show')
              <a href="{{ route('grupos.usuarios', $grupo->id) }}" class="btn btn-sm bg-blue">Usuarios</a>
              @endcan
            </td>

            <td width="30px">
              @can('grupos.edit')
              <a href="{{ route('grupos.edit', $grupo->id) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a>
              @endcan
            </td>
            <td width="30px">
              @can('grupos.destroy')
              {!! Form::open(['route' => ['grupos.destroy', $grupo->id], 'method' => 'DELETE']) !!}
              <button class="btn btn-sm bg-red btndelete"><i class="far fa-trash-alt"></i></button>
              {!! Form::close() !!}
              @endcan
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <div class="pt-2 float-right">
        {{ $grupos->render() }}
      </div>

    </div>
  </div>
</section>
@endsection