@extends('layouts.appback')

@section('content')
  <header class="page-header">
    <div class="container-fluid">
      <div class="d-flex flex-row align-items-center">
        <div class="pr-4">
           <h2 class="no-margin-bottom">{{ $encuesta->titulo}}</h2>
        </div>
        <div class="ml-auto">
          <a class="btn btn-light" href="{{ route('encuestas.index') }}"><i class="fa fa-arrow-left"></i></a>
        </div>
      </div>
    </div>
  </header>

  <div class="mt-4">
    <div class="container-fluid">
      <div class="d-flex flex-row align-items-center">
        <div>
          <p class='m-0'><strong>Listado de Preguntas</strong></p>
        </div>

        <div class="ml-auto">
          @can('encuestas_preguntas.create')
        <a href="{{ route('encuestas_preguntas.create') }}" class="btn bg-green"><i class="fa fa-plus"></i> Crear Pregunta</a>
        @endcan
        </div>
      </div>
    </div>
  </div>
  
  <section class="tables section">   
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">

            <div class="table-responsive bg-white">                       
            <table class="table table-hover ">
              <thead class="bg-dark">
                <tr>
                  <th>#</th>
                  <th>Encuesta</th>
                  <th>Pregunta</th>
                  <th>Tipo de pregunta</th>
                  <th colspan="3">&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                @foreach($encuestas_preguntas as $encuestas_pregunta)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ ($encuestas_pregunta->encuesta) ? $encuestas_pregunta->encuesta->titulo : ''   }}</td>
                        <td>{{ $encuestas_pregunta->titulo }}</td>
                        <td>{{ config( 'constantes.tipopreg.' . $encuestas_pregunta->tipo_pregunta) }}</td>
                        <td width="30px">
                            @can('encuestas_preguntas.edit')
                               <a href="{{ route('encuestas_preguntas.edit', $encuestas_pregunta->id) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a> 
                            @endcan
                        </td>
                        <td width="30px">
                            @can('encuestas_preguntas.destroy')
                                {!! Form::open(['route' => ['encuestas_preguntas.destroy', $encuestas_pregunta->id], 'method' => 'DELETE']) !!}
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
                {{ $encuestas_preguntas->render() }}
            </div>

        </div>
      </div>
    </div>
  </section>
@endsection
