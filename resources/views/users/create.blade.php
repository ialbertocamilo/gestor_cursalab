@extends('layouts.appback')

@section('content')
@include('layouts.user-header')
<section class="section-list">
  <header class="page-header">
    <div class="container-fluid">
      <div class="d-flex flex-row align-items-center">
        <div class="pr-4">
          <h5 class="no-margin-bottom">Administrador</h5>
        </div>
        <div>
          @can('users.create')
          <a href="{{ route('users.create') }}" class="btn bg-green float-right"><i class="fa fa-plus"></i> Crear</a>
          @endcan
        </div>
      </div>
    </div>
  </header>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center">
          <h5>Crear Administrador</h5>
          <button class="btn btn-light ml-auto" onclick="history.back()"><i class="fa fa-arrow-left"></i></button>
        </div>
        <div class="card-body">
          {!! Form::open(['route' => 'users.store']) !!}

          @include('users.partials.form')

          {!! Form::close() !!}

        </div>
      </div>
    </div>
  </div>
</section>
@endsection