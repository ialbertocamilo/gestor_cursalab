@extends('layouts.appback')

@section('content')
  <header class="page-header">
    <div class="container-fluid">
            <div class="d-flex flex-row align-items-center">
            <div class="pr-4">
                <h2 class="no-margin-bottom">Áreas</h2>
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
              <h3 class="h4">Crear</h3>
              <button class="btn btn-light ml-auto" onclick="history.back()"><i class="fa fa-arrow-left"></i></button>
            </div>
            <div class="card-body">
              {!! Form::open(['route' => 'areas.store']) !!}

                @include('areas.partials.form')

              {!! Form::close() !!}

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
