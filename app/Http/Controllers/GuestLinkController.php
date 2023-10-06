<?php

namespace App\Http\Controllers;

use App\Models\GuestLink;
use Illuminate\Http\Request;

class GuestLinkController extends Controller
{
    public function addUrl(Request $request){
        $data = GuestLink::addUrl($request->all());
        return $this->success($data);
    }

    public function initData(){
        $data = GuestLink::initData();
        return $this->success($data);
    }
    
    public function listGuestUrl(){
        $data = GuestLink::listGuestUrl();
        return $this->success($data);
    }
    public function verify_guest_url_multimarca($url){
        $data = GuestLink::verify_guest_url_multimarca($url);
        return $this->success($data);
    }
    
    public function destroy($url_id){
        GuestLink::deleteGuestLink($url_id);
        return $this->success(['msg'=>'La URL ha sido eliminada correctamente.']);
    }

    public function register_user(Request $request){
        $data = GuestLink::register_user($request->get('user'));
        return $this->success($data);
    }
}
