<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Http\Resources\GuestResource;
use App\Http\Requests\GuestStoreRequest;
use App\Http\Requests\GuestInvitationRequest;

class GuestController extends Controller
{
    public function search(Request $request)
    {
        $data = Guest::searchForGrid($request);
        GuestResource::collection($data);
        return $this->success($data);
    }
    public function deleteGuest(Request $request)
    {
        $data = Guest::deleteGuest($request->get('guest_ids'));
        return $this->success($data);
    }

    public function sendInvitation(GuestInvitationRequest $request){
        $data = Guest::sendInvitationByEmail($request->get('email'));
        return $this->success($data);
    }

    public function activateMultipleUsers(Request $request) {

        // $result = Usuario::whereIn('id',  $request->usersIds)->update(['estado' => 1]);
        
        return $this->success([]);

    }
    public function limitsWorspace(){
        $data = Workspace::infoLimitCurrentWorkspace();
        return $this->success($data);
    }
}
