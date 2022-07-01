@extends('layouts.appback')

@section('content')
@include('layouts.user-header')
<section class="section-list">
    <header class="page-header">
    <div class="container-fluid">
        <div class="d-flex flex-row align-items-center justify-content-between">
        <h5 class="no-margin-bottom">Procesos masivos</h5>
        </div>
    </div>
    </header>
</section>
<v-app>
  <subida-masiva :info_error="{{$info_error}}"></subida-masiva>
</v-app>
  {{-- <section class="section">   
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12">
            <p style="color: #777;">
                <strong>Instrucciones:</strong><br>
                - Adjunta un archivo CSV o excel con los datos requeridos .<br>
                - Los datos y IDs deben estar en el orden indicado.<br>
                - Al subir el archivo, quitar la cabecera.<br>
            </p>
            @if(Session::has('no_procesados'))
              @if(!empty(Session::get('no_procesados')))
              <div class="resumen row">
                <div class="col-sm-12 mb-3">
                  <div class="client card">
                    <div class="card-body">
                      <p><strong>Estos usuarios no fueron procesados por inconsistencias en el archivo excel subido:</strong></p>
                      <ul class="list-group">
                        @foreach(Session::get('no_procesados') as $item)
                          <li class="list-group-item">{{ $item }}</li>
                        @endforeach
                      </ul> 
                    </div>
                  </div>
                </div>
              </div>
              @endif
            @endif
            @if(session('info_insert') || session('info_errors') || session('info_updates') || session('info_cambios') || session('info_cesados') || session('info_activos'))
              <div class="alert alert-success" role="alert">
                @if(session('info_insert'))
                Se han registrado {{session('info_insert')}} nuevo(s) usuario(s). </br>
                @endif
                @if(session('info_updates'))
                  Se ha encontrado {{session('info_updates')}} usuario(s) actualizado(s).</br>
                @endif
                @if(session('info_cambios'))
                  Se ha encontrado {{session('info_cambios')}} usuario(s) cambiado(s).</br>
                @endif
                @if(session('info_cesados'))
                  Se han inactivado(s) {{session('info_cesados')}} usuario(s).</br>
                @endif
                @if(session('info_activos'))
                  Se han activado(s) {{session('info_activos')}} usuario(s).</br>
                @endif
                @if(session('info_errors'))
                  Se han encontrado {{session('info_errors')}} error(es).
                @endif
              </div>
            @endif
            @if(session('error_page'))
                <div class="alert alert-danger" role="alert">
                  {{session('error_page')}}
                </div>
            @endif
            <div class="resumen row">
              <div class="col-sm-6 mb-3">
                <div class="client card">
                  <div class="card-body text-center">

                    <form action="{{ route('masivo.subir_usuarios') }}" method="POST" class="row formu" enctype="multipart/form-data"> 
                      @csrf
                      <div class="col-sm-12 card-icon">
                        <i class="fas fa-users-cog" style="color:#4266e8;"></i>
                      </div>

                      <div class="col-sm-12 client-title">
                        <h2>Subida masiva de usuarios</h2>
                        <p>Se crea o actualiza los datos de usuarios según el valor de la columna de acción.</p>
                        <span>Columnas del excel: Módulo, DNI, nombres y apellidos, botica, grupo, cargo, genero, carrera, ciclo, acción</span>
                        <br />
                        <span>Accion: Nuevo, datos</span>
                        <hr>
                      </div>
                      <div class="col-sm-8 mb-3 offset-sm-2" >
                        <input type="file" name="file_usuarios" id="file_usuarios" class="form-control required" required>
                      </div>
                      <div class="col-sm-12 mt-3">
                          <button type="submit" class="btn btn-md btn-primary btn_dw"><i class="fas fa-play"></i> <span>Empezar</span> </button>
                      </div>   
                    </form>
                    @if($info_error['err_usu']>0)
                      @include('masivo.modal_errors')
                    @endif      
             
                  </div>
                </div>
              </div>
              <div class="col-sm-6 mb-3">
                <div class="client card">
                  <div class="card-body text-center">
                    <form action="{{ route('masivo.actualizar_ciclo') }}" method="POST" class="row formu" enctype="multipart/form-data"> 
                      @csrf
                      <div class="col-sm-12 card-icon">
                        <i class="fas fa-walking" style="color:#4266e8;"></i>
                      </div>

                      <div class="col-sm-12 client-title">
                        <h2>Migracion de usuarios y actualización de ciclos</h2>
                        <p>Se actualizan los ciclos de los usuarios y se migran según el valor de la columna de acción.</p>
                        <span>Columnas del excel: Módulo, DNI, nombres y apellidos, botica, grupo, cargo, genero, carrera, ciclo, acción</span>
                        <br />
                        <span>Accion: Cambio de carrera, Cambio de módulo, Cambio de ciclo</span>
                        <hr>
                      </div>
                      
                      <div class="col-sm-8 mb-3 offset-sm-2" >
                        <input type="file" name="dnis_file" id="dnis_file" class="form-control required" required>
                      </div>

                      <div class="col-sm-12 mt-3">
                          <button type="submit" class="btn btn-md btn-primary btn_dw"><i class="fas fa-play"></i> <span>Empezar</span> </button>
                      </div>   
                    </form>
                    @if($info_error['err_cambio']>0)
                      @include('masivo.me_ciclos_carr')
                    @endif   
                  </div>
                </div>
              </div>

              <div class="col-sm-6 mb-3">
                <div class="client card">
                  <div class="card-body text-center">

                    <form action="{{ route('masivo.migrar_usuarios') }}" method="POST" class="row formu" enctype="multipart/form-data"> 
                      @csrf
                      <div class="col-sm-12 card-icon">
                        <i class="fas fa-people-arrows"></i>
                      </div>

                      <div class="col-sm-12 client-title">
                        <h2>Migración de carreras</h2>
                        <p>Se migra el avance de cada usuario (matrículas, evaluaciones, encuestas y visitas)</p>
                        <span>Columnas del excel: DNI, modulo, carrera, ciclo</span>
                        <hr>
                      </div>
                      <div class="col-sm-8 mb-3 offset-sm-2" >
                        <input type="file" name="dnis_file" id="dnis_file" class="form-control required" required>
                      </div>
                      <div class="col-sm-12 mt-3">
                          <button type="submit" class="btn btn-md btn-primary btn_dw"><i class="fas fa-play"></i> <span>Empezar</span> </button>
                      </div>   
                    </form>
                  </div>
                </div>
              </div>

              <div class="col-sm-6 mb-3">
                <div class="client card">
                  <div class="card-body text-center">

                    <form action="{{ route('masivo.cesar_usuarios') }}" method="POST" class="row formu" enctype="multipart/form-data"> 
                      @csrf
                      <div class="col-sm-12 card-icon">
                        <i class="fas fa-user-slash" style="color:#be4bdb;"></i>
                      </div>

                      <div class="col-sm-12 client-title">
                        <h2>Desactivar(Cesar) usuarios</h2>
                        <p>Se cambia el estado del usuario a 0(inactivo)</p>
                        <span>Columnas del excel: Módulo, DNI, nombres y apellidos, botica, grupo, cargo, genero, carrera, ciclo, acción</span>
                        <hr>
                      </div>
                      <div class="col-sm-8 mb-3 offset-sm-2" >
                        <input type="file" name="dnis_file" id="dnis_file" class="form-control required" required>
                      </div>
                      <div class="col-sm-12 mt-3">
                          <button type="submit" class="btn btn-md btn-primary btn_dw"><i class="fas fa-play"></i> <span>Empezar</span> </button>
                      </div>   
                    </form>
                    @if($info_error['err_desct_usu']>0)
                      @include('masivo.me_cesados')
                    @endif   
                  </div>
                </div>
              </div>
              <div class="col-sm-6 mb-3">
                <div class="client card">
                  <div class="card-body text-center">
                    <form action="{{ route('masivo.recuperar_data_cesados') }}" method="POST" class="row formu" enctype="multipart/form-data"> 
                      @csrf
                      <div class="col-sm-12 card-icon">
                        <i class="fas fa-user-check" style="color:#2d79ec"></i>
                      </div>
                      <div class="col-sm-12 client-title">
                        <h2>Activar Usuarios</h2>
                        <p>Se cambia el estado del usuario a 1(activo)</p>
                        <span>Columnas del excel: Módulo, DNI, nombres y apellidos, botica, grupo, cargo, genero, carrera, ciclo, acción</span>
                        <hr>
                      </div>
                      
                      <div class="col-sm-8 mb-3 offset-sm-2" >
                        <input type="file" name="file" id="file" class="form-control required" required>
                      </div>
                      <div class="col-sm-12 mt-3">
                          <button type="submit" class="btn btn-md btn-primary btn_dw"><i class="fas fa-play"></i> <span>Empezar</span> </button>
                      </div>   
                    </form>
                    @if($info_error['err_activ_usu']>0)
                      @include('masivo.me_rec_cesados')
                    @endif  
                  </div>
                </div>
              </div>
              <div class="col-sm-6 mb-3">
                <div class="client card">
                  <div class="card-body text-center">

                    <form action="{{ route('masivo.migrar_farma_historial') }}" method="POST" class="row formu" enctype="multipart/form-data"> 
                      @csrf
                      <div class="col-sm-12 card-icon">
                        <i class="fas fa-random" style="color:#ec2d2d;"></i>
                      </div>

                      <div class="col-sm-12 client-title">
                        <h2>Migrar usuarios de farma historial a farma universidad</h2>
                        <p>Se migra el avance de un usuario desde la base de datos farma_historial a la base de datos farma_universidad</p>
                        <span>Columnas del excel: Módulo, DNI, nombres y apellidos, botica, grupo, cargo, genero, carrera, ciclo, acción</span>
                        <hr>
                      </div>
                      <div class="col-sm-8 mb-3 offset-sm-2" >
                        <input type="file" name="file" id="file" class="form-control required" required>
                      </div>
                      <div class="col-sm-12 mt-3">
                          <button type="submit" class="btn btn-md btn-primary btn_dw"><i class="fas fa-play"></i> <span>Empezar</span> </button>
                      </div>   
                    </form>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 mb-3">
                <div class="client card">
                  <div class="card-body text-center">

                    <form action="{{ route('masivo.migrar_avance_x_curso') }}" method="POST" class="row formu" enctype="multipart/form-data"> 
                      @csrf
                      <div class="col-sm-12 card-icon">
                        <i class="fas fa-random" style="color:#ec2d2d;"></i>
                      </div>

                      <div class="col-sm-12 client-title">
                        <h2>Trasladar avance de un curso a otro</h2>
                        <p>Se migra el avance de un curso específico a otro curso para todos los usuarios de la plataforma</p>
                        <span>Columnas del excel: CURSO_ORIGEN_ID, TEMA_ORIGEN_ID, CURSO_DESTINO_ID, TEMA_DESTINO_ID</span>
                        <hr>
                      </div>
                      
                      <div class="col-sm-8 mb-3 offset-sm-2" >
                        <input type="file" name="file" id="file" class="form-control required" required>
                      </div>

                      <div class="col-sm-12 mt-3">
                          <button type="submit" class="btn btn-md btn-primary btn_dw"><i class="fas fa-play"></i> <span>Empezar</span> </button>
                      </div>   
                    </form>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 mb-3">
                <div class="client card">
                  <div class="card-body text-center">
                    <form action="{{ route('masivo.restaurar_bd2019') }}" method="POST" class="row formu" enctype="multipart/form-data"> 
                      @csrf
                      <div class="col-sm-12 card-icon">
                        <i class="fas fa-retweet" style="color:#2d79ec"></i>
                      </div>
                      <div class="col-sm-12 client-title">
                        <h2>Recuperar data de usuario de la BD 2019</h2>
                        <p>Se recupera el avance del usuario de la base de datos del 2019</p>
                        <hr>
                      </div>
                      <input class="form-control" type="text" placeholder="DNI" name="dni" value="">
                      <div class="col-sm-12 mt-3">
                        <button type="submit" class="btn btn-md btn-primary btn_dw"><i class="fas fa-play"></i> <span>Empezar</span> </button>
                    </div>  
                    </form>
                  </div>
                </div>
              </div>
            </div>
        </div> 
      </div>
    </div>
  </section> --}}
@endsection