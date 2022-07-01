@extends('layouts.appback')

@section('content')
<header class="page-header">
    <div class="breadcrumb-holder container-fluid">
      <ul class="breadcrumb ">
        <li class="breadcrumb-item"><a href="{{ route('tipo_criterio.index') }}">Tipo de Criterio</a></li>
        <li class="breadcrumb-item active"><span>Crear</span></li>
      </ul>

    </div>
</header>

<section class="forms section">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header d-flex align-items-center">
              <h3 class="h4">Crear</h3>
              <button class="btn btn-light ml-auto" onclick="history.back()"><i class="fa fa-arrow-left"></i></button>
            </div>
            <div class="card-body">
              {!! Form::open(['route' => 'tipo_criterio.store', 'enctype' => 'multipart/form-data']) !!}

                @include('tipo_criterio.partials.form')

              {!! Form::close() !!}

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
