@extends('layouts.appback')

@section('content')
    @include('layouts.user-header')
    <v-app>
        {{-- <meetings-layout/> --}}
        <meetings-layout :usuario_id="{{ Auth::user()->id }}" :workspace_id="{{ get_current_workspace()->id  }}" :superuser="{{ Auth::user()->isAn('super-user') ? true : false  }}"/>
        {{-- <aulas-virtuales-layout :usuario_id="{{ Auth::user()->id }}" :workspace_id="1"/> --}}
    </v-app>
@endsection
