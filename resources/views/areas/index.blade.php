@extends('layouts.appback')

@section('content')
  <header class="page-header">
    <div class="container-fluid">
            <div class="d-flex flex-row align-items-center">
            <div class="pr-4">
                <h2 class="no-margin-bottom">Áreas</h2>
            </div>
            <div>
                @can('areas.create')
                    <a href="{{ route('areas.create') }}" class="btn bg-green float-right"><i class="fa fa-plus"></i> Crear</a>
                @endcan
            </div>
            <div class="ml-auto">
                <form id="searchForm" action="#" role="search" >
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
  
  <section class="tables section">   
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">

            <div class="table-responsive bg-white">                       
            <table class="table table-hover ">
              <thead class="bg-dark">
                <tr>
                  <!-- <th>#</th> -->
                  <th>Nombre</th>
                  <th>Estado</th>
                  <th colspan="3">&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                @foreach($areas as $area)
                    <tr>
                        <!-- <th scope="row">{{ $area->id }}</th> -->
                        <td>{{ $area->nombre }}</td>
                        <td>{{ ($area->estado == 1) ? "Activo" : "Inactivo"  }}</td>

                        <td width="30px">
                            @can('areas.show')
                               <a href="{{ route('areas.usuarios', $area->id) }}" class="btn btn-sm bg-blue">Usuarios</a> 
                            @endcan
                        </td>

                        <!-- <td width="30px">
                            @can('areas.show')
                               <a href="{{ route('areas.show', $area->id) }}" class="btn btn-sm bg-violet">Ver</a> 
                            @endcan
                        </td> -->

                        <td width="30px">
                            @can('areas.edit')
                               <a href="{{ route('areas.edit', $area->id) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a> 

                            @endcan
                        </td>
                        <td width="30px">
                            @can('areas.destroy')
                                {!! Form::open(['route' => ['areas.destroy', $area->id], 'method' => 'DELETE']) !!}
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
                {{ $areas->render() }}
            </div>

        </div>
      </div>
    </div>
  </section>
@endsection
