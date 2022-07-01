@extends('layouts.appback')

@section('content')
@include('layouts.user-header')
<section class="section-list">
  <header class="page-header">
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <h5 class="no-margin-bottom">Permisos</h5>
          <div class="ml-2">
            @can('permisos.create')
            <a href="{{ route('permisos.create') }}" class="btn bg-green float-right"><i class="fa fa-plus"></i> Crear</a>
            @endcan
          </div>
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

      <div class="table-responsive bg-white">
        <table class="table table-hover ">
          <thead class="bg-dark">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Slug</th>
              <th>Descripción</th>
              <th colspan="3">&nbsp;</th>
            </tr>
          </thead>
          <tbody>
            @foreach($permisos as $permiso)
            <tr>
              <th scope="row">{{ $permiso->id }}</th>
              <td>{{ $permiso->name }}</td>
              <td>{{ $permiso->slug }}</td>
              <td>{{ $permiso->description }}</td>
              <td width="30px">
                @can('permisos.edit')
                <a href="{{ route('permisos.edit', $permiso->id) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a>
                @endcan
              </td>
              <td width="30px">
                @can('permisos.destroy')
                {!! Form::open(['route' => ['permisos.destroy', $permiso->id], 'method' => 'DELETE']) !!}
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
        {{ $permisos->render() }}
      </div>

    </div>
  </div>
</section>
@endsection