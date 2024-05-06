<?php

namespace App\Http\Controllers;

use App\Models\Error;
use App\Models\SectionUpload;
use App\Models\School;
use App\Models\Taxonomy;
use App\Models\Workspace;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MassiveUploadTopicGrades;
use App\Http\Requests\MassiveUploadTopicGradesRequest;


class UploadTopicGradesController extends Controller
{
    public function index()
    {
        return view('masivo.upload-topic-grades');
    }

    public function getFormSelects()
    {
        $workspace = get_current_workspace();

        $qualified_type = Taxonomy::getFirstData('topic', 'evaluation-type', 'qualified');
        // Load modules
        // $modules = Workspace::where('parent_id', $workspace->id)
        // ->select('id', 'name')
        // ->get();

        $modules = Workspace::where('parent_id', $workspace?->id)
            ->whereIn('id', current_subworkspaces_id())
            ->select('id', 'name')->get();

        // $modules_id = $workspace->subworkspaces->pluck('id')->toArray();
        $modules_id = current_subworkspaces_id();
        // Load workspace's schools
        $schools = School::with([
            'subworkspaces:id,name',
            'courses' => function ($q) use ($qualified_type) {
                $q->with([
                    'topics' => function ($q2) use ($qualified_type) {
                        $q2->where('assessable', 0)
                        ->orWhere('type_evaluation_id', $qualified_type->id)
                        ->select('id', 'name', 'assessable', 'course_id');
                    }
                ])
                    ->select('id', 'name', 'type_id', 'assessable');
            }
        ])
        ->whereHas('subworkspaces', function ($j) use ($modules_id) {
            $j->whereIn('subworkspace_id', $modules_id);
        })
        ->select('id', 'name')
        ->get();

        $directions = config('massive.upload-topic-grades');

        return $this->success(compact('schools', 'directions', 'modules'));
    }

    public function upload(MassiveUploadTopicGradesRequest $request)
    {
        try {
            //code...
            $data = $request->validated();
    //        if (count($data['topics'] ? []) === 0) return $this->error('No se ha seleccionado ningún tema.');

            $import = new MassiveUploadTopicGrades($data);
            Excel::import($import, $data['file']);

            // === guardar archivo log ===
            $codes = [ 'code_section' => 'upload-notes',
                       'code_type' => 'upload' ];
            SectionUpload::storeRequestLog($request, $codes);
            // === guardar archivo log ===

            $info = $import->getNoProcesados();
            $msg = "Se subieron correctamente las notas.";
            return $this->success(compact('info', 'msg'));
        } catch (\Throwable $exception) {
            //throw $th;

            Error::storeAndNotificateException($exception, $request);
            return response()->json(['message' => 'Ha ocurrido un problema. Contáctate con el equipo de soporte.'], 500);
        }
    }
}
