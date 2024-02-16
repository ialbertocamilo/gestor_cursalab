<?php

namespace App\Http\Controllers\Induction\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\CheckList;
use App\Models\ChecklistRpta;
use App\Models\ChecklistRptaItem;
use App\Models\EntrenadorUsuario;
use App\Models\Process;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestProcessController extends Controller
{

    public function getProcesses()
    {
        $user = Auth::user();
        $data = [
            'user' => $user ?? null,
        ];
        $apiResponse = Process::getProcessesApi($data);

        return response()->json($apiResponse, 200);
    }

    public function getProcess(Process $process)
    {
        $user = Auth::user();
        $data = [
            'process' => $process?->id,
            'user' => $user
        ];
        $apiResponse = Process::getProcessApi($data);

        return response()->json($apiResponse, 200);
    }
}
