@extends('layouts.appback')

@section('content')
    @php
        
        $project  = \App\Models\Project::with('course:id,name')->where('id',request()->segment(2))->select('id','course_id')->first();

    @endphp
    <v-app>
        @include('layouts.user-header')
        <project-users-layout project_id="{{$project->id}}" course_name="{{$project->course->name}}"  />
    </v-app>
@endsection