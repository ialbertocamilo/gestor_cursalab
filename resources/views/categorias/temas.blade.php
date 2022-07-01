@extends('layouts.appback')

@section('content')
  <header class="page-header">
    <div class="container-fluid">
      <div class="d-flex flex-row align-items-center">
        <div class="pr-4">
          <p>Escuela:</p>
           <h2 class="no-margin-bottom">{{ $categoria->nombre}}</h2>
        </div>
        <div class="ml-auto">
          <a class="btn btn-light" href="{{ route('categorias.index') }}"><i class="fa fa-arrow-left"></i></a>
        </div>
      </div>
    </div>
  </header>
  
  <div class="mt-4">
    <div class="container-fluid">
      <div class="d-flex flex-row align-items-center">
        <div>
          <p class='m-0'><strong>Listado de Temas</strong></p>
        </div>

        <div class="ml-auto">
          @can('posteos.create')
        <a href="{{ route('posteos.create') }}" class="btn bg-green"><i class="fa fa-plus"></i> Crear Tema</a>
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
                  <!-- <th>#</th> -->
                  <th>Nombre</th>
                  <!-- <th>Tipo de posteo</th> -->
                  <th>Orden</th>
                  <th>Evaluable</th>
                  <th>Requisito</th>

                  <th colspan="4">&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                @foreach($posteos as $posteo)
                    <tr>
                        <!-- <th scope="row">{{ $posteo->id }}</th> -->
                        <td>{{ $posteo->nombre }}</td>                        
                        <td>{{ $posteo->orden }}</td>
                        <td>{{ $posteo->evaluable }}</td>
                        <td>{{ ($posteo->posteo_requisito) ? $posteo->posteo_requisito->nombre : '-' }}</td>     

                        <td width="30px">
                            @can('posteos.show')
                               <a href="{{ route('posteos.preguntas', $posteo->id) }}" class="btn btn-sm bg-blue">Evaluación</a> 

                            @endcan
                        </td>
                        <td width="30px">
                            @can('posteos.edit')
                               <a href="{{ route('posteos.edit', $posteo->id) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a> 
                            @endcan
                        </td>
                        <td width="30px">
                            @can('posteos.destroy')
                                {!! Form::open(['route' => ['posteos.destroy', $posteo->id], 'method' => 'DELETE']) !!}
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
                {{ $posteos->render() }}
            </div>

        </div>
      </div>
    </div>
  </section>
@endsection
