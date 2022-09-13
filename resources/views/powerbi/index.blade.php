@section('content')

<v-app>
  
  @include('layouts.user-header')

  <learning-analytics-embed :pbi_url="'{{ $pbi_url ?? '' }}'" />

</v-app>

@endsection