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
                <form id="searchForm" action="#" role="search">
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
  
  <section class="tables section">   
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">

            <div class="table-responsive bg-white">                       
            <table class="table table-hover ">
              <thead class="bg-dark">
                <tr>
                  <th>#</th>
                  <th>Respuestas</th>
                  <th>Tipo de pregunta</th>
                  <th>Pregunta</th>
                   <th>Usuario</th>
                  <th colspan="3">&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                @foreach($encuestas_respuestas as $encuestas_respuesta)
                    <tr>
                        <th scope="row">{{ $encuestas_respuesta->id }}</th>
                        <td>{{ $encuestas_respuesta->respuestas }}</td>
                        <td>{{ $encuestas_respuesta->tipo_pregunta }}</td>
                        
                        <td>{{ ($encuestas_respuesta->pregunta) ? $encuestas_respuesta->pregunta->pregunta : '' }}</td>
                        <td>{{ ($encuestas_respuesta->usuario) ? $encuestas_respuesta->usuario->nombre.' '.$encuestas_respuesta->usuario->ap_paterno : '' }}</td>  
                        <td width="30px">
                            @can('encuestas_respuestas.show')
                               <a href="{{ route('encuestas_respuestas.show', $encuestas_respuesta->id) }}" class="btn btn-sm bg-violet">Ver</a> 

                            @endcan
                        </td>
                        <td width="30px">
                            @can('encuestas_respuestas.edit')
                               <a href="{{ route('encuestas_respuestas.edit', $encuestas_respuesta->id) }}" class="btn btn-sm bg-orange">Editar</a> 
                            @endcan
                        </td>
                        <td width="30px">
                            @can('encuestas_respuestas.destroy')
                                {!! Form::open(['route' => ['encuestas_respuestas.destroy', $encuestas_respuesta->id], 'method' => 'DELETE']) !!}
                                    <button class="btn btn-sm bg-red btndelete">Eliminar</button>
                                {!! Form::close() !!}
                            @endcan
                        </td>
                    </tr>
                @endforeach
              </tbody>
            </table>
            </div>

            <div class="pt-2 float-right">
                {{ $encuestas_respuestas->render() }}
            </div>

        </div>
      </div>
    </div>
  </section>
@endsection
