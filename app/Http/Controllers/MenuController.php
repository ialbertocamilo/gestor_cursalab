<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function list(){
        $data = Menu::list();
        return $this->success($data);
    }

    public function updateItems(Request $request){
        $data = $request->all();
        Menu::updateItems($data);
        return $this->success(['msg'=>'Se actualizó correctamente']);
    }
}
