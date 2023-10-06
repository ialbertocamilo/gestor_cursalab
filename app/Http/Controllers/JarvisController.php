<?php

namespace App\Http\Controllers;

use App\Models\Jarvis;
use Illuminate\Http\Request;

class JarvisController extends Controller
{
    public function generateDescriptionJarvis(Request $request){
        $course_name  = $request->get('course_name');
        $description = Jarvis::generateDescriptionJarvis($course_name);
        return $this->success(compact('description'));
    }
}
