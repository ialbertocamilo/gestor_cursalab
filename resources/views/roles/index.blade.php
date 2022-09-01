@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')
    <section class="section-list">
        <header class="page-header">
            <div class="container-fluid">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <h5 class="no-margin-bottom">Roles</h5>
                        <div class="ml-2">
                            @if ($super_user)
                                <a href="{{ route('roles.create') }}" class="btn bg-green float-right"><i class="fa fa-plus"></i>
                                    Crear</a>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="ml-auto">
                            <form id="searchForm" action="#" role="search">
                                <div class="input-group">
                                    <input type="search" name='q' class="form-control" placeholder="Palabra clave"
                                        aria-label="Palabra clave" aria-describedby="basic-addon2">
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary" type="submit"><i
                                                class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="row">
            <div class="col-12">

                <div class="table-responsive bg-white">
                    <table class="table table-hover ">
                        <thead class="bg-dark">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Name</th>
                                <th>Descripción</th>
                                <th colspan="3">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <th scope="row">{{ $role->id }}</th>
                                    <td>{{ $role->title }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>{{ $role->description }}</td>
                                    <!--                        <td width="10px">
                                        @can('roles.show')
                                            <a href="{{ route('roles.show', $role->id) }}" class="btn btn-sm bg-violet">Ver</a>
                                        @endcan
                                    </td>-->
                                    <td width="10px">
                                        @if ($super_user)
                                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm bg-orange"><i
                                                    class="fas fa-edit"></i></a>
                                        @endif
                                    </td>
                                    <td width="10px">
                                        @if ($super_user)
                                            {!! Form::open(['route' => ['roles.destroy', $role->id], 'method' => 'DELETE']) !!}
                                            <button class="btn btn-sm bg-red btndelete"><i
                                                    class="far fa-trash-alt"></i></button>
                                            {!! Form::close() !!}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- <div class="pt-2 float-right">
        {{ $roles->render() }}
      </div> --}}

            </div>
        </div>
    </section>
@endsection
