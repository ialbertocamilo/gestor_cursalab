@extends('layouts.appback')

@section('content')
  <header class="page-header">
    <div class="container-fluid">
      <div class="d-flex flex-row align-items-center">
        <div class="pr-4">
            <h2 class="no-margin-bottom">Mallas</h2>
        </div>
      </div>
    </div>
  </header>

  <section class="forms section">   
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header d-flex align-items-center">
              <h3 class="h4">Editar</h3>
            </div>
            <div class="card-body">
              {!! Form::model($malla, ['route' => ['mallas.update', $malla->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) !!}
                
                @include('mallas.partials.form')

              {!! Form::close() !!}

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
