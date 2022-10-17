<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\CheckList;
use App\Models\ChecklistRpta;
use App\Models\ChecklistRptaItem;
use App\Models\EntrenadorUsuario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestChecklistController extends Controller
{

    public function alumnos(Request $request)
    {
        $user = Auth::user();
        $data = [
            'entrenador_dni' => $user->document,
            'filtro' => $request->filtro
        ];
        $apiResponse = EntrenadorUsuario::alumnosApi($data);

        return response()->json($apiResponse, 200);
    }

    public function getChecklistsByAlumno(User $alumno)
    {
        $response = CheckList::getChecklistsByAlumno($alumno->id);

        return response()->json($response, 200);
    }

    public function marcarActividad(Request $request)
    {
        $alumno_id = $request->alumno_id;
        $entrenador = EntrenadorUsuario::where('user_id', $alumno_id)->first();
        $checklist_id = $request->checklist_id;

        $estado = $request->estado;
        $actividad_id = $request->actividad_id;

        $checklistRpta = ChecklistRpta::checklist($checklist_id)->alumno($alumno_id)->entrenador($entrenador->trainer_id)->first();

        $checklistRptaItem = ChecklistRptaItem::where('checklist_answer_id', $checklistRpta->id)->where('checklist_item_id', $actividad_id)->first();
        if (!$checklistRptaItem) {
            $checklistRptaItem = ChecklistRptaItem::create([
                'checklist_answer_id' => $checklistRpta->id,
                'checklist_item_id' => $actividad_id,
                'qualification' => $estado
            ]);
        }
        $checklistRptaItem->qualification = $estado;
        $checklistRptaItem->save();

        ChecklistRpta::actualizarChecklistRpta($checklistRpta);

        return response()->json([
            'error' => false,
            'msg' => 'Actividad actualizada.'
            /*'checklist_rpta_item' => $checklistRptaItem*/
        ], 200);
    }
}
