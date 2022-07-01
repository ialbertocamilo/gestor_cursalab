@extends('layouts.appback')

@section('content')
  <header class="page-header">
    <div class="container-fluid">
            <div class="d-flex flex-row align-items-center">
            <div class="pr-4">
                <h2 class="no-margin-bottom">Encuestas</h2>
            </div>
            <div>
                @can('encuestas.create')
                    <a href="{{ route('encuestas.create') }}" class="btn bg-green float-right"><i class="fa fa-plus"></i> Crear</a>
                @endcan
            </div>
            <div class="ml-auto">
                <form id="searchForm" action="" role="search" >
                  <div class="input-group">
                  <input type="search" name="q" class="form-control" placeholder="Palabra clave" aria-label="Palabra clave" aria-describedby="basic-addon2">
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
                  <th>#</th>
                  <th>Titulo</th>
                  <th>Tipo</th>
                  <th>Anónima</th>
                  <th>Estado</th>
                  <th colspan="3">&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                @foreach($encuestas as $encuesta)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $encuesta->titulo }}</td>
                        <?php $array1 = [ 'xcurso'=> 'PARA CURSOS', 'libre'=> 'LIBRE' ]; ?>
                        <td>{{ ($encuesta->tipo) ? $array1[$encuesta->tipo] : '' }}</td>
                        <?php $arr_anonima = [ 'si'=> 'SI', 'no'=> 'NO' ]; ?>
                        <td>{{ ($encuesta->anonima) ? $arr_anonima[$encuesta->anonima] : '' }}</td>
                        <td>{{ ($encuesta->estado == 1) ? "Activo" : "Inactivo"  }}</td>

                          <td width="30px">
                            @can('encuestas.show')
                               <a href="{{ route('encuestas.preguntas', $encuesta->id) }}" class="btn btn-sm bg-blue">Preguntas</a> 
                            @endcan
                        </td>

                        <td width="30px">
                            @can('encuestas.edit')
                               <a href="{{ route('encuestas.edit', $encuesta->id) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a> 
                            @endcan
                        </td>
                        <td width="30px">
                            @can('encuestas.destroy')
                                {!! Form::open(['route' => ['encuestas.destroy', $encuesta->id], 'method' => 'DELETE']) !!}
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
                {{ $encuestas->render() }}
            </div>

        </div>
      </div>
    </div>
  </section>
@endsection
