<?php

namespace App\Http\Controllers\Induction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{

    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing([
            'workspace_id' => $workspace?->id
        ]);

        $data = collect();


        return $this->success($data);
    }
}
