<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function search(Request $request)
    {
        // $data = Guest::searchForGrid($request);
        // GuestResource::collection($data);
        // return $this->success($data);
        return $this->success([]);
    }

    public function listGuestUrl(){
        $data = Guest::listGuestUrl();
        return $this->success(compact('data'));
    }

    public function send_invitation(Request $request){
        // $data = Guest::send_email_invitation($request->get('email'));
        // return $this->success($data);
        return $this->success([]);

    }

    public function activateMultipleUsers(Request $request) {

        // $result = Usuario::whereIn('id',  $request->usersIds)->update(['estado' => 1]);

        return $this->success([]);

    }
}
