<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Jarvis;
use App\Models\Workspace;
use Illuminate\Http\Request;

class JarvisController extends Controller
{
    public function generateDescriptionJarvis(Request $request){
        $description = Jarvis::generateDescriptionJarvis($request);
        return $this->success(compact('description'));
    }
    public function generateQuestionsJarvis(Request $request){
        $questions = Jarvis::generateQuestionsJarvis($request);
        return $this->success($questions);
    }
    public function getLimitsByWorkspace(Request $request){
        $topic = ($request->topic_id) ? Topic::find($request->topic_id) : null;
        $limits = Workspace::getLimitAIConvert($topic,$request->type);
        return $this->success($limits);
    }

    public function generateChecklistJarvis(Request $request){
        $checklist = Jarvis::generateChecklistJarvis($request);
        return $this->success($checklist);
    }
}
