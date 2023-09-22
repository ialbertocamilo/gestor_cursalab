<?php

namespace App\Http\Controllers;

use App\Models\RegisterUrl;
use Illuminate\Http\Request;

class RegisterUrlController extends Controller
{
    public function addUrl(Request $request){
        $data = RegisterUrl::addUrl($request->all());
        return $this->success($data);
    }
    public function verify_guest_url($url){
        $data = RegisterUrl::verify_guest_url($url);
        return $this->success($data);
    }

    public function verify_guest_url_multimarca($url){
        $data = RegisterUrl::verify_guest_url_multimarca($url);
        return $this->success($data);
    }
    
    public function destroy($url_id){
        RegisterUrl::delete_guest_url($url_id);
        return $this->success(['msg'=>'La URL ha sido eliminada correctamente.']);
    }

    public function register_user(Request $request){
        $data = RegisterUrl::register_user($request->get('user'));
        return $this->success($data);
    }
}
