<?php

namespace App\Http\Controllers;

use App\Models\Taxonomy;
use App\Models\Block;

use Illuminate\Http\Request;
use App\Http\Requests\BlockRequest;
use App\Http\Resources\BlockResource;
// use App\Http\Controllers\ZoomApi;

class LearningBlockController extends Controller
{
    public function search()
    {
        $blocks = Block::search($request);

        BlockResource::collection($blocks);

        return $this->success($blocks);
    }

}
