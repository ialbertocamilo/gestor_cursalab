@extends('layouts.appback', ['fullScreen' => true])

@section('morecss')
@endsection

@section('content')
    <div class="row bg-white justify-content-center">
        <div class="col-10">
            @include('layouts.user-header')
        </div>
    </div>
    <v-app>
        <criterion-layout-wk />
    </v-app>
@endsection
