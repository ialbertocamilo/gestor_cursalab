<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Models\Block;
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
//        $users = User::withWhereHas('criterion_values', function ($q) {
//            $q->select('id', 'value_text');
//        })
//            ->limit(10)->get();
//
//        return $this->success($users);
        $user = User::find(38);

        $user->setCurrentCourses();

        return $this->success($user);
    }

    public function blocks()
    {
        $blocks = Block::query()
            ->withWhereHas('segments', function ($q) {
                $q->select('segments.id', 'segments.name');
            })
            ->withCount('criterion_values')
            ->limit(10)->get();

        $programs = [];
        foreach ($blocks as $block) {
            $programs[] = [
                'name' => $block->name,
                'criterion_values_count' => $block->criterion_values_count,
                'roadmap' => $block->segments,
            ];
        }

        return $this->success($programs);
    }
}
