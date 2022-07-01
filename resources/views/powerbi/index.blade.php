@extends('layouts.appback')

@section('content')
<v-app>
  @include('layouts.user-header')
  <learning-analytics-embed :pbi_url="'{{env('PBI_URL')}}'" />
</v-app>

@endsection