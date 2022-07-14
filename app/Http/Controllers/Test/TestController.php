<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\School;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function courses()
    {
        $courses = Course::withWhereHas('topics', function ($q) {
            $q->select('id', 'name', 'course_id');
        })
            ->select('id', 'name')
            ->limit(10)->get();

        return $this->success($courses);
    }

    public function schools()
    {
        $schools = School::withWhereHas('courses', function ($q) {
            $q->select('courses.id', 'courses.name');
        })
            ->select('id', 'name')
            ->limit(10)->get();

        return $this->success($schools);
    }


    public function workspaces()
    {
        $workspaces = Workspace::with('schools:id,name')
            ->select('id', 'name')
            ->limit(10)->get();

        return $this->success($workspaces);
    }

    public function users()
    {
        $users = User::withWhereHas('criterion_values', function ($q){
            $q->select('id', 'value_text');
        })
            ->limit(10)->get();

        return $this->success($users);
    }
}
