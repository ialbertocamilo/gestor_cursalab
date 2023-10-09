<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\GuestLink;
use Illuminate\Http\Request;
use App\Http\Requests\GuestStoreRequest;
use App\Models\User;

class GuestRestController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function verifyGuestUrl(Request $request){
        // $ip = $request->ip();
        // $host = gethostbyaddr($ip);
        $data = GuestLink::verifyGuestUrl($request);
        return $this->success($data);
    }
    public function childCriterionValues(Request $request){
        $data = GuestLink::childCriterionValues($request);
        return $this->success($data);
    }

    public function storeGuest(GuestStoreRequest $request){
        $data = $request->validated();
        $message = Guest::storeGuest($data,$request);
        return $this->success($message);
    }
    public function sendGuestCodeVerificationByEmail(Request $request){
        $data = Guest::sendGuestCodeVerificationByEmail($request);
        return $this->success($data);
    }
    
    public function verifyGuestCodeVerificationByEmail(Request $request){
        $data = Guest::verifyGuestCodeVerificationByEmail($request);
        return $this->success($data);
    }

}
