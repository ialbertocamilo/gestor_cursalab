@extends('layouts.appback')

@section('content')
  <header class="page-header">
    <div class="container-fluid">
      <div class="d-flex flex-row align-items-center">
        <div class="pr-4">
            <h2 class="no-margin-bottom">Mallas</h2>
        </div>
        <div>
            @can('mallas.create')
                <a href="{{ route('mallas.create') }}" class="btn bg-green float-right"><i class="fa fa-plus"></i> Crear</a>
            @endcan
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
                    <th>Módulo</th>
                    <th>Perfil</th>
                    <th>Archivo</th>
                    <th colspan="3">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($mallas as $malla)
                      <tr>
                          <!-- <th scope="row">{{ $malla->id }}</th> -->
                          <?php 
                            $array = config( 'constantes.modalidad' );
                          ?>
                          <td>{{ $malla->config->etapa }}</td>
                          <td>{{ $malla->perfil->nombre }}</td>
                          <td>
                            @if($malla->archivo)
                              <a href="{{ asset($malla->archivo) }}" target="_blank">Ver</a>
                            @endif
                          </td>
                          <td width="30px">
                              @can('mallas.edit')
                                 <a href="{{ route('mallas.edit', $malla->id) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a> 
                              @endcan
                          </td>
                          <td width="30px">
                              @can('mallas.destroy')
                                  {!! Form::open(['route' => ['mallas.destroy', $malla->id], 'method' => 'DELETE']) !!}
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
                {{ $mallas->render() }}
            </div>

        </div>
      </div>
    </div>
  </section>
@endsection
