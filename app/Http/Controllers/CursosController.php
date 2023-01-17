<?php

namespace App\Http\Controllers;

use App\Http\Requests\Curso\CursoEncuestaStoreUpdate;
use App\Http\Requests\Curso\CursosStoreUpdateRequest;
use App\Http\Requests\Curso\MoverCursoRequest;
use App\Http\Requests\CursoStoreRequest;

use App\Http\Resources\Curso\CursoSearchResource;
use App\Models\Taxonomy;
use App\Models\Workspace;
use DB;

// use App\Perfil;
// use App\Curso_perfil;
// use App\Posteo_perfil;
use App\Models\Media;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Carrera;
use App\Models\Abconfig;
use App\Models\Poll;
use App\Models\Pregunta;
use App\Models\Categoria;
use App\Models\Curricula;
use App\Models\Curso_encuesta;

use Illuminate\Http\Request;

use App\Http\Controllers\ApiRest\HelperController;
use App\Models\Course;
use App\Models\Requirement;
use App\Models\School;

class CursosController extends Controller
{

    public function search(School $school, Request $request)
    {
        $request->merge(['school_id' => $school->id ?? null]);

        $cursos = Course::search($request);

        CursoSearchResource::collection($cursos);

        return $this->success($cursos);
    }

    public function getFormSelects(School $school, Course $course = null, $compactResponse = false)
    {
        $workspace = get_current_workspace();

        $query = Course::whereRelation('workspaces', 'id', $workspace->id)->where('active', ACTIVE);

        if ($course)
            $query->where('id', '!=', $course->id);

        $req_cursos = $query->get();

        $escuelas = School::
            whereRelation('workspaces', 'workspace_id', $workspace->id)
            ->get()->map(function ($school, $key) {
            $suffix = !$school->active ? " [Inactivo]" : "";
            $school->name = $school->name . "{$suffix}";
            return $school;
        });

        $requisitos = collect();

        $types = Taxonomy::getSelectData('course', 'type');

        foreach ($req_cursos as $req) {
            $requisitos->push((object)[
                'id' => $req->id,
                'name' => $req->name,
                'escuelas' => '---'
            ]);
        }

        $response = compact('escuelas', 'requisitos', 'types');

        return $compactResponse ? $response : $this->success($response);
    }

    public function searchCurso(School $school, Course $course)
    {
        // $scheduled_restarts = json_decode($course->scheduled_restarts);
        $scheduled_restarts = $course->scheduled_restarts;
        $course->scheduled_restarts_activado = $scheduled_restarts['activado'] ?? false;
        $course->scheduled_restarts_dias = $scheduled_restarts['reinicio_dias'] ?? 0;
        $course->scheduled_restarts_horas = $scheduled_restarts['reinicio_horas'] ?? 0;
        $course->scheduled_restarts_minutos = $scheduled_restarts['reinicio_minutos'] ?? 0;
        $form_selects = $this->getFormSelects($school, $course, true);
        $course->makeHidden('scheduled_restarts');

        $req_curso = Requirement::whereHasMorph('model', [Course::class], function ($query) use ($course) {
            $query->where('id', $course->id);
        })->first();

        $course->requisito_id = (!is_null($req_curso)) ? $req_curso->requirement_id : '';

        $schools = collect();
        foreach ($course->schools as $req) {
            $schools->push((object)[
                'id' => $req->id,
                'nombre' => $req->name,
            ]);
        }

        $course->lista_escuelas = $schools;

        return $this->success([
            'curso' => $course,
            'requisitos' => $form_selects['requisitos'],
            'escuelas' => $form_selects['escuelas'],
            'types' => $form_selects['types'],
        ]);
    }

    public function storeCurso(School $school, CursosStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data['school_id'] = ($school->exists) ? $school->id : null;
        $data['escuelas'] = $request->lista_escuelas;

        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        Course::storeRequest($data);

        $response = [
            'msg' => 'Curso creado correctamente.',
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function updateCurso(School $school, Course $course, CursosStoreUpdateRequest $request)
    {
        $data = $request->validated();
//        info($data);

        if ($data['validateForm']):
            $validations = Course::validateBeforeUpdate($data, $school, $course);
            if (count($validations['list']) > 0)
                return $this->success(compact('validations'), 'Ocurrió un error', 422);
        endif;

        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        $data['school_id'] = ($school->exists) ? $school->id : null;
        $data['escuelas'] = $request->lista_escuelas;
//        $data['active'] = ($data['active'] === 'true' or $data['active'] === true) ? 1 : 0;

        $response_curso = Course::storeRequest($data, $course);
        $response = [
            'curso' => $response_curso,
            'msg' => 'Curso actualizado correctamente.',
            'messages' => Course::getMessagesAfterUpdate($course, 'Curso actualizado correctamente.')
        ];

        return $this->success($response);
    }

    public function destroyCurso(School $school, Course $course, Request $request)
    {
        if ($request->validateForm):
            $validations = Course::validateBeforeDelete(['active' => true], $school, $course);
            if (count($validations['list']) > 0)
                return $this->success(compact('validations'), 'Ocurrió un error', 422);
        endif;

        // TODO: Compatibles: Actualizar si se elimina el curso
        $course->updateOnModifyingCompatibility();

        $course->delete();
        $course->requirements()->delete();


        $response = [
            'curso' => $course,
            'msg' => 'Curso eliminado correctamente.',
            'messages' => Course::getMessagesAfterDelete($course)
        ];

        return $this->success($response);
    }

    public function updateStatus(School $school, Course $course, Request $request)
    {
        $update_status_to = !$course->active;

        if ($request->validateForm):
            $validations = Course::validateBeforeUpdate(['active' => $update_status_to], $school, $course);
            if (count($validations['list']) > 0)
                return $this->success(compact('validations'), 'Ocurrió un error', 422);
        endif;

        $course->active = $update_status_to;
        $course->save();

        // TODO: Compatibles: Actualizar si se modifica el estado del curso
        if ($course->wasChanged('active')):

            $course->updateOnModifyingCompatibility();

        endif;

        $response = [
            'curso' => $course,
            'msg' => 'Estado actualizado con éxito.',
            'messages' => Course::getMessagesAfterUpdate($course, 'Curso actualizado correctamente.')
        ];

        return $this->success($response);
    }

    ////////////////////// CURSO ENCUESTA ////////////////////////////

    public function getEncuesta(School $school, Course $course)
    {
        $workspace = get_current_workspace();

        $encuestas = Poll::select('titulo as nombre', 'id')->where('workspace_id', $workspace->id)->get();
        $encuestas->prepend(['nombre' => 'Ninguno', 'id' => "ninguno"]);
        $course->encuesta_id = $course->polls->first()->id ?? "ninguno";

        return $this->success(compact('encuestas', 'course'));
    }

    public function storeUpdateEncuesta(School $school, Course $course, CursoEncuestaStoreUpdate $request)
    {
        $data = $request->validated();

        $polls = $data['encuesta_id'] === "ninguno" ? [] : $data['encuesta_id'];

        $course->polls()->sync($polls);

        cache_clear_model(Course::class);

        return $this->success(['msg' => 'Encuesta de curso actualizada.']);
    }

    public function create_CE(Curso $curso)
    {
        $encuestas_array = Poll::select('id', 'titulo')->pluck('titulo', 'id');
        $encuestas_array->prepend('NINGUNO', '');

        return view('cursos.create_encuesta', compact('encuestas_array', 'curso'));
    }

    public function store_CE(Curso $curso, Request $request)
    {

        $ce = new Curso_encuesta;
        $ce->curso_id = $request->curso_id;
        $ce->encuesta_id = $request->encuesta_id;

        if ($request->encuesta_id != "" || $request->encuesta_id != NULL) {
            $ce->save();
        }

        return redirect()->route('categorias.cursos', [$curso->categoria->config->id, $curso->categoria->id])
            ->with('info', 'Registro guardado con éxito');
    }

    public function edit_CE(Curso $curso, Curso_encuesta $ce)
    {

        $encuestas_array = Poll::select('id', 'titulo')->where('tipo', 'xcurso')->where('estado', 1)->pluck('titulo', 'id');
        $encuestas_array->prepend('NINGUNO', '');
        // return $encuestas_array;
        return view('cursos.edit_encuesta', compact('curso', 'ce', 'encuestas_array'));
    }

    public function update_CE(Curso $curso, Request $request, Curso_encuesta $ce)
    {

        $ce->curso_id = $request->curso_id;
        $ce->encuesta_id = $request->encuesta_id;
        if ($request->encuesta_id != "" || $request->encuesta_id != NULL) {
            $ce->save();
        } else {
            $ce->delete();
        }

        return redirect()->route('categorias.cursos', [$curso->categoria->config->id, $curso->categoria->id])
            ->with('info', 'Registro actualizado con éxito');
    }

    public function destroy_CE(Curso $curso, Curso_encuesta $ce)
    {
        $ce->delete();

        return redirect()->route('categorias.cursos', [$curso->categoria->config->id, $curso->categoria->id])->with('info', 'Eliminado Correctamente');
    }

    public function getCompatibilities(Course $course)
    {
        $workspace = get_current_workspace();
        $compatibilities = $course->getCompatibilities();
        $courses = Course::select('id', 'name')
                        ->whereRelation('workspaces', 'id', $workspace->id)
                        ->where('id', '<>', $course->id)
                        ->get();

        return $this->success(compact('compatibilities', 'courses'));
    }

    public function updateCompatibilities(Course $course, Request $request)
    {
        $courses = $request->compatibilities;

        $data['compatibilities'] = array_column($courses, 'id');

        Course::storeCompatibilityRequest($course, $data);

        $response = [
            // 'curso' => $course,
            'msg' => 'Compatibilidad de curso actualizado correctamente.',
        ];

        return $this->success($response);
    }

    // public function moverCurso(Abconfig $abconfig, Categoria $categoria, Curso $curso, MoverCursoRequest $request)
    // {
    //     $data = $request->validated();
    //     $validate = Curso::validateMoverCurso($curso);
    //     //        dd($validate);
    //     if (!$validate['validate'])
    //         return $this->success(compact('validate'), 422);

    //     $curso = Curso::moverCurso($curso, $data['escuela_id']);

    //     return $this->success(['msg' => 'El curso se movió correctamente.']);
    // }

    public function getSelects()
    {
        $workspace = get_current_workspace();

        $types = Taxonomy::getSelectData('course', 'type');

        return $this->success(compact('types'));
    }



    // ====================================== SEGMENTATION COURSE ===================================

    public function getFiltersSelects()
    {
        $workspace = get_current_workspace();

        $modules = Workspace::where('parent_id', $workspace->id)
            ->select('criterion_value_id as id', 'name')
            ->get();

        $schools = School::whereRelation('workspaces', 'id', $workspace->id)
            ->select('id', 'name')
            ->get();

        return $this->success(compact('modules', 'schools'));
    }

    public function getEncuestaSegmentation(Course $course)
    {
        $school = $course->schools()->first();
        return $this->getEncuesta($school, $course);
    }

    public function storeUpdateEncuestaSegmentation(Course $course, CursoEncuestaStoreUpdate $request)
    {
        $school = $course->schools()->first();
        return $this->storeUpdateEncuesta($school, $course, $request);
    }

    public function searchCursoSegmentation(Course $course)
    {
        $school = $course->schools()->first();

        return $this->searchCurso($school, $course);
    }

    public function updateCursoSegmentation(Course $course, CursosStoreUpdateRequest $request)
    {
        $school = $course->schools()->first();

        return $this->updateCurso($school, $course, $request);
    }

}
