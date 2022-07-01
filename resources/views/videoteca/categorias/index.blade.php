@extends('layouts.appback')

@section('content')
    <header class="page-header">
        <div class="container-fluid">
            <div class="d-flex flex-row align-items-center">
                <div class="pr-4"
                     style="display: flex; justify-content: center; flex-direction: row; align-items: center">
                    <a href="{{ route('videoteca.list') }}" class="btn"><i
                            class="fa fa-arrow-circle-left"></i></a>
                    <h2 class="no-margin-bottom">Categorías Videoteca</h2>
                </div>
                <div>

                    @can('videoteca.categorias_create')
                        <a href="{{ route('videoteca.categorias_create') }}" class="btn bg-green"><i
                                class="fa fa-plus"></i> Crear</a>
                    @endcan
                </div>
            </div>
        </div>
    </header>

    <section class="client">
        <div class="container-fluid">
            <div class="row">

                <div class="col-12">
                    <form id="searchForm" action="#" role="search">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="search" name='q' class="form-control" placeholder="Palabra clave"
                                           aria-label="Palabra clave" aria-describedby="basic-addon2"
                                           value="{{ request('q') }}">
                                    &nbsp;
                                    <button class="btn btn-secondary" type="submit"><i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">

                <div class="col-12">
                    <br>
                    <div class="table-responsive bg-white">
                        <table class="table table-hover ">
                            <thead class="bg-dark">
                            <tr>
                                <th align="center">#</th>
                                <th align="center">Nombre</th>
                                <th style="width: 20%" class="text-center">Opciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($categorias_videoteca as $elemento)
                                <tr>
                                    <th scope="row">{{ $elemento->id }}</th>
                                    <td scope="row">{{ $elemento->nombre }}</td>
                                    <td class="d-flex justify-content-center">
                                        @can('videoteca.categorias_edit')
                                            <a href="{{ route('videoteca.categorias_edit', $elemento->id) }}"
                                               class="btn btn-sm bg-orange mx-3"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('videoteca.categorias_destroy')
                                            {!! Form::open(['route' => ['videoteca.categorias_destroy', $elemento->id], 'method' => 'DELETE']) !!}
                                            <button class="btn btn-sm bg-red btndelete mx-3"><i
                                                    class="far fa-trash-alt "></i></button>
                                            {!! Form::close() !!}
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="5">No se encontraron registros</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-2 float-right">
                        {{ $categorias_videoteca->appends(request()->except('page'))->render() }}
                    </div>

                </div>

            </div>

        </div>
    </section>

@endsection


@section('js')

    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <style type="text/css">
        .select2 .selection {
            width: 100%;
        }

        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            border: 1px solid #dee2e6;
            border-radius: 0;
            padding: 0.375rem 0.75rem;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border: solid #dee2e6 1px;
            outline: 0;
        }

        .select2-container--default .select2-selection--multiple {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 0px;
            cursor: text;
        }

    </style>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.select2').select2();
            $('.select2-multiple').select2({
                placeholder: "Principios activos"
            });
        });
    </script>


@endsection
