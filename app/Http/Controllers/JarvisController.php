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
}
