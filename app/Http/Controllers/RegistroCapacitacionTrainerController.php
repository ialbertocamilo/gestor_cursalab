<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\RegistroCapacitacionTrainer;
use Illuminate\Http\Request;

class RegistroCapacitacionTrainerController extends Controller
{
    public function storeRequest(Request $request){
        $data = $request->all();
        $file = $request->file('file_signature');
        $item = RegistroCapacitacionTrainer::storeDataRequest($data, $file);
        return $this->success($item);
    }
}
