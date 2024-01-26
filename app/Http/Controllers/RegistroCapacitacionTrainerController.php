<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\RegistroCapacitacionTrainer;
use Illuminate\Http\Request;

class RegistroCapacitacionTrainerController extends Controller
{
    public function storeRequest(Request $request){
        $data = $request->all();
        $item = RegistroCapacitacionTrainer::storeDataRequest($data);
        return $this->success($item);
    }
}
