<?php

namespace App\Http\Controllers;

use App\Models\Jarvis;
use Illuminate\Http\Request;

class JarvisController extends Controller
{
    public function generateDescriptionJarvis(Request $request){
        $description = Jarvis::generateDescriptionJarvis($request);
        return $this->success(compact('description'));
    }
    public function generateQuestionsJarvis(Request $request){
        $questions = Jarvis::generateQuestionsJarvis($request);
        return $this->success(compact('questions'));
    }

}
