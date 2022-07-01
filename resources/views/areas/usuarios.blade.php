@extends('layouts.appback')

@section('content')


 <header class="page-header">
    <div class="container-fluid">
      <div class="d-flex flex-row align-items-center">
        <div class="pr-4">
          <p>Area:</p>
           <h2 class="no-margin-bottom">{{ $area->nombre}}</h2>
        </div>
        <div class="ml-auto">
          <a class="btn btn-light" href="{{ route('areas.index') }}"><i class="fa fa-arrow-left"></i></a>
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
                  <th>Apellido paterno</th>
                  <th>Apellido materno</th>
                  <th>DNI</th>
                  <th>Perfil</th>

                  <th colspan="2">&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                @foreach($usuarios as $usuario)
                    <tr>
                        <!-- <th scope="row">{{ $usuario->id }}</th> -->
                        <td>{{ $usuario->nombre }}</td>                        
                        <td>{{ $usuario->ap_paterno }}</td>                                              
                        <td>{{ $usuario->ap_materno }}</td>
                        <td>{{ $usuario->dni }}</td>
                        <td>{{ ($usuario->perfil) ? $usuario->perfil->nombre : '' }}</td>  

                        <!-- <td width="30px">
                            @can('usuarios.show')
                               <a href="{{ route('usuarios.show', $usuario->id) }}" class="btn btn-sm bg-violet">Ver</a> 
                            @endcan
                        </td> -->
                        <td width="30px">
                            @can('usuarios.edit')
                               <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a>
                            @endcan
                        </td>
                        <td width="30px">
                            @can('usuarios.destroy')
                                {!! Form::open(['route' => ['usuarios.destroy', $usuario->id], 'method' => 'DELETE']) !!}
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
                {{ $usuarios->render() }}
            </div>

        </div>
      </div>
    </div>
  </section>
@endsection
