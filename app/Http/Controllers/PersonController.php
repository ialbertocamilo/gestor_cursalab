<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function storeRequest(Request $request){
        $data = $request->all();
        $person = Person::storeDataRequest($data);
        return $this->success($person);
    }
}
