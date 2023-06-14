<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\CheckList;
use App\Models\ChecklistRpta;
use App\Models\ChecklistRptaItem;
use App\Models\EntrenadorUsuario;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestBenefitController extends Controller
{

    public function getBenefits(Request $request)
    {
        $user = Auth::user();
        $data = [
            'filtro' => $request->filtro,
            'status' => $request->status ?? null,
            'user' => $user?->id ?? null,
            'page' => $request->page ?? null
        ];
        $apiResponse = Benefit::getBenefits($data);

        return response()->json($apiResponse, 200);
    }

    public function getInfo(Benefit $benefit)
    {
        $user = Auth::user();
        $data = [
            'benefit' => $benefit,
        ];
        $apiResponse = Benefit::getInfo($data);

        return response()->json($apiResponse, 200);
    }

    public function getInfoSpeaker(Speaker $speaker)
    {
        $user = Auth::user();
        $data = [
            'speaker' => $speaker,
        ];
        $apiResponse = Speaker::getInfo($data);

        return response()->json($apiResponse, 200);
    }

    public function registerUserForBenefit(Request $request)
    {
        $user = Auth::user();
        $data = [
            'user' => $user?->id ?? null,
            'benefit' => $request->benefit ?? null,
        ];

        $apiResponse = Benefit::registerUserForBenefit($data);

        return response()->json($apiResponse, 200);
    }
}
