@extends('layouts.appback')

@section('content')
  <header class="page-header">
    <div class="container-fluid">
            <div class="d-flex flex-row align-items-center">
            <div class="pr-4">
                <h2 class="no-margin-bottom">Pruebas</h2>
            </div>
            <!-- <div>
                @can('pruebas.create')
                    <a href="{{ route('pruebas.create') }}" class="btn bg-green float-right"><i class="fa fa-plus"></i> Crear</a>
                @endcan
            </div> -->
            <!-- <div class="ml-auto">
                <form id="searchForm" action="#" role="search" >
                  <div class="input-group">
                  <input type="search" class="form-control" placeholder="Palabra clave" aria-label="Palabra clave" aria-describedby="basic-addon2">
                  <div class="input-group-append">
                    <button class="btn btn-secondary" type="button"><i class="fa fa-search"></i></button>
                  </div>
                </div>
                </form>
            </div> -->
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
                  <th>#</th>
                  <th>Tema</th>
                  <th>Usuario</th>
                  <th>Intentos</th>
                  <th>Respuestas Correctas</th>
                  <th>Respuestas Incorrectas</th>
                  <th>Nota</th>
                  <th>Resultado</th>

                  <th colspan="3">&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pruebas as $prueba)
                    <tr> 
                        <th scope="row">{{ $prueba->id }}</th>
                        <td>{{ ($prueba->posteo) ? $prueba->posteo->nombre : '' }}</td> 
                        <td>{{ ($prueba->usuario) ? $prueba->usuario->nombre : '' }}</td> 
                        <td>{{ $prueba->intentos }}</td>
                        <td>{{ $prueba->rptas_ok }}</td>
                        <td>{{ $prueba->rptas_fail }}</td>
                        <td>{{ $prueba->nota }}</td>
                        <td>{{ $prueba->resultado }}</td>
                        <!-- <td>{{ $prueba->ubicacion }}</td>
                        <td>{{ $prueba->created_at }}</td>
                        <td>{{ $prueba->udated_at }}</td>-->
                        <td width="30px">
                            @can('pruebas.show')
                               <a href="{{ route('pruebas.show', $prueba->id) }}" class="btn btn-sm bg-violet">Ver</a> 

                            @endcan
                        </td>
                        <!-- <td width="30px">
                            @can('pruebas.edit')
                               <a href="{{ route('pruebas.edit', $prueba->id) }}" class="btn btn-sm bg-orange">Editar</a> 

                            @endcan
                        </td> -->
                        <!-- <td width="30px">
                            @can('pruebas.destroy')
                                {!! Form::open(['route' => ['pruebas.destroy', $prueba->id], 'method' => 'DELETE']) !!}
                                    <button class="btn btn-sm bg-red btndelete">Eliminar</button>
                                {!! Form::close() !!}
                            @endcan
                        </td> -->
                    </tr>
                @endforeach
              </tbody>
            </table>
            </div>

            <div class="pt-2 float-right">
                {{ $pruebas->render() }}
            </div>

        </div>
      </div>
    </div>
  </section>
@endsection
