@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')
    <section class="section-list">
        <header class="page-header">
            <div class="container-fluid">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <h5 class="no-margin-bottom">Administrador</h5>
                        <div class="ml-2">
                            @if ($super_user)
                                <a href="{{ route('users.create') }}"
                                   class="btn bg-green float-right">
                                    <i class="fa fa-plus"></i>
                                    Crear
                                </a>
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
                                <th>Nombre</th>
                                <th>Email</th>
                                <th colspan="3">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <th scope="row">{{ $user->id }}</th>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    @if ($super_user)
                                        <td width="10px">
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm bg-orange"><i
                                                    class="fas fa-edit"></i></a>
                                        </td>
                                        <td width="10px">
                                            {!! Form::open(['route' => ['users.destroy', $user->id], 'method' => 'DELETE']) !!}
                                            <button class="btn btn-sm bg-red btndelete"><i
                                                    class="far fa-trash-alt"></i></button>
                                            {!! Form::close() !!}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pt-2 float-right">
                    {{ $users->render() }}
                </div>

            </div>
        </div>
    </section>
@endsection
