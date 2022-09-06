@extends('layouts.appback', ['fullScreen' => true])

@section('morecss')
@endsection

@section('content')
    <div class="row bg-white justify-content-center">
        <div class="col-10">
            @include('layouts.user-header')
        </div>
    </div>
    @php
    use App\Models\Criterion;
    $criterion = Criterion::find(request()->segment(3));
    @endphp
    <v-app>
        <criterion-value-layout criterion_id="{{ request()->segment(3) }}" criterion_name="{{ $criterion->name ?? '' }}" />
    </v-app>
@endsection
