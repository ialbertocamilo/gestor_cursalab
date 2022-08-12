@extends('layouts.appback')

@section('content')
    @php
        use App\Models\Criterion;
        $criterion = Criterion::find(request()->segment(2));
    @endphp
    <v-app>
        <criterion-value-layout
            criterion_id="{{ request()->segment(2) }}"
            criterion_name="{{$criterion->name ?? ''}}"
        />
    </v-app>
@endsection
