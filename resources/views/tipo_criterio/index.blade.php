@extends('layouts.appback')

@section('content')
@include('layouts.user-header')

<header class="page-header">
<section class="section-list">
    <header class="page-header">
      <div class="container-fluid">
        <div class="d-flex flex-row align-items-center justify-content-between">
          <h5 class="no-margin-bottom">Tipo de criterio</h5>
          <div>
            {{-- @can('tipo_criterio.create')
              <a href="{{ route('tipo_criterio.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Crear</a>
            @endcan --}}
          </div>
        </div>
      </div>
    </header>
  </section>
<section class="client">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="client card">
          <div class="card-body text-center">
            <form action="#">
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    {{ Form::search('q', request('q'), ['class' => 'search-table form-control', 'placeholder' => 'Buscar...']) }}
                  </div>
                </div>
              </div>
            </form>
            <div class="table-responsive">
              <table class="table m-t-30 datatable table-hover" data-page-size="50" id="grid">
                <thead>
                  <tr>
                    <th class="no-search text-center">Orden</th>
                    <th class="text-center">Nombre</th>
                    <th class="text-center">Nombre plural</th>
                    <th class="text-center">Tipo de dato</th>
                    <th class="no-sort no-search text-center">Opciones</th>
                  </tr>
                </thead>
                <tbody id="data-content">
                  @forelse($tipo_criterios as $tipo_criterio)
                  <tr>
                    <td>
                      @include('default.fractals.order', ['model' => $tipo_criterio, 'all' => $tipo_criterios, 'route' => 'tipo_criterio.change-order'])
                    </td>
                    <td>
                      {{ $tipo_criterio->nombre }}
                    </td>
                    <td>
                      {{ $tipo_criterio->nombre_plural }}
                    </td>
                    <td>
                      {{ $tipo_criterio->data_type}}
                    </td>
                    <td> 
                       <a href="{{ route('criterio.index', $tipo_criterio->id) }}" class="btn btn-md" title="Ver Criterios">
                                    <i class="fas fa-ruler"></i>
                                    <br> <span class="icon-title">Criterios</span>
                                </a>
                                <span class="badge badge-notify"> {{$tipo_criterio->criterios_count}} </span>
                               {{-- <a href="{{ route('tipo_criterio.edit', $tipo_criterio) }}" class="btn btn-sm bg-orange"><i class="fas fa-edit"></i></a> --}}
                               <a href="{{ route('tipo_criterio.edit', $tipo_criterio) }}" class="btn btn-md" title="Editar Criterio">
                                    <i class="mdi mdi-pencil" aria-hidden="true"></i>
                                    <br> <span class="icon-title">Editar</span>
                               </a>
                            </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="10">No se encontraron registros</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="pt-2 float-right">
              {{ $tipo_criterios->appends(request()->except('page'))->render() }}
            </div>

          </div>
        </div>
      </div>

    </div>

  </div>
</section>

@endsection