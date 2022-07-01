@extends('layouts.appback')

@section('content')
@include('layouts.user-header')
<section class="section-list">
  <header class="page-header">
    <div class="container-fluid">
      <div class="d-flex flex-row align-items-center">
        <div class="pr-4">
          <h2 class="no-margin-bottom">Escuelas</h2>
        </div>
        <div>
          @can('categorias.create')
          <a href="{{ route('categorias.create') }}" class="btn bg-green float-right"><i class="fa fa-plus"></i> Crear</a>
          @endcan
        </div>
        <div class="ml-auto">
          <form id="searchForm" action="" role="search">
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
  </header>

  <div class="row">
    <div class="col-12">

      <div class="table-responsive bg-white">
        <table class="table table-hover ">
          <thead class="bg-dark">
            <tr>
              <!-- <th>#</th> -->
              <th>Orden</th>
              <th>Nombre</th>
              <th>Imagen</th>
              <th>Modalidad</th>
              <th>Módulo</th>
              <th>Estado</th>
              <th colspan="4">&nbsp;</th>
            </tr>
          </thead>
          <tbody>
            @foreach($categorias as $categoria)
            <tr>
              <!-- <th scope="row">{{ $categoria->id }}</th> -->
              <?php
              $array = config('constantes.modalidad');
              ?>
              <td>{{ $categoria->orden }}</td>
              <td>{{ $categoria->nombre }}</td>
              <td><img src="{{ asset($categoria->imagen) }}" alt="" width="120"></td>
              <td>{{ (isset($array[$categoria->modalidad])) ? $array[$categoria->modalidad] : '' }}</td>
              <td>{{ $categoria->config->etapa }}</td>
              <td>{{ ($categoria->estado == 1) ? "Activo" : "Inactivo" }}</td>


              <td width="30px">
                @can('cursos.show')
                <a href="{{ route('categorias.cursos', $categoria->id) }}" class="btn btn-sm bg-green">Cursos</a>

                @endcan
              </td>

              <td width="30px">
                @can('categorias.show')
                <a href="{{ route('categorias.temas', $categoria->id) }}" class="btn btn-sm bg-blue">Temas</a>

                @endcan
              </td>
              <td width="30px">
                @can('categorias.edit')
                <a href="{{ route('categorias.edit', $categoria->id) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a>
                @endcan
              </td>
              <td width="30px">
                @can('categorias.destroy')
                {!! Form::open(['route' => ['categorias.destroy', $categoria->id], 'method' => 'DELETE']) !!}
                <button class="btn btn-sm bg-red btndelete"><i class="far fa-trash-alt"></i></button>
                {!! Form::close() !!}
                @endcan
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="pt-2 float-right">
        {{ $categorias->render() }}
      </div>

    </div>
  </div>
</section>
@endsection