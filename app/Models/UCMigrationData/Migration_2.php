<?php

namespace App\Models\UCMigrationData;

use App\Models\Course;
use App\Models\School;
use App\Models\Support\OTFConnection;
use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Migration_2 extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    public function insertChunkedData($data, $table_name)
    {
        foreach ($data as $chunk) {
            DB::table($table_name)->insert($chunk);
        }
    }

    public function makeChunkAndInsert($data, $table_name)
    {
        $chunk = array_chunk($data, self::CHUNK_LENGTH, true);

        $this->insertChunkedData($chunk, $table_name);
    }

    protected function migrateEscuelas()
    {
        $escuelas_data = self::setEscuelasData();

        self::insertEscuelasData($escuelas_data);
    }

    protected function migrateCursos()
    {
        $cursos_data = self::setCursosData();

        self::insertCursosData($cursos_data);
    }

    protected function migrateTemas()
    {
        $temas_Data = self::setTemasData();

        self::insertTemasData($temas_Data);
    }

    protected function setEscuelasData()
    {
        $db = self::connect();

        $categorias = $db->getTable('categorias')
            ->select(
                'id', 'nombre', 'descripcion', 'imagen', 'orden',
                'plantilla_diploma', 'reinicios_programado',
                'estado', 'created_at', 'updated_at')
            ->get();
        $data = [];

        foreach ($categorias as $escuela) {
            $data[] = [
                'external_id' => $escuela->id,

                'name' => $escuela->nombre,
                'description' => $escuela->descripcion,

                'imagen' => $escuela->imagen,
                'plantilla_diploma' => $escuela->plantilla_diploma,

                'position' => $escuela->orden,

                'scheduled_restarts' => $escuela->reinicios_programado,

                'active' => $escuela->estado,
                'created_at' => $escuela->created_at,
                'updated_at' => $escuela->updated_at,
            ];
        }

        return $data;
    }

    protected function insertEscuelasData($data)
    {
        $this->makeChunkAndInsert($data, 'schools');

        $uc_workspace = Workspace::where('slug', 'universidad-corporativa')->first();

        $schools = School::all();
        $school_workspace = [];

        foreach ($schools as $school) {
            $school_workspace[] = [
                'workspace_id' => $uc_workspace->id,
                'school_id' => $school->id,
            ];
        }

        $this->makeChunkAndInsert($school_workspace, 'school_workspace');
    }

    protected function setCursosData()
    {
        $db = self::connect();

        $cursos = $db->getTable('cursos')
            ->select(
                'id', 'nombre', 'descripcion', 'imagen', 'orden',
                'plantilla_diploma', 'reinicios_programado', 'c_evaluable', 'libre',
                'categoria_id', 'requisito_id',
                'estado', 'created_at', 'updated_at')
            ->get();
        $data = [];

        foreach ($cursos as $curso) {
            if ($curso->requisito_id):

                $data['curso_requisitos'][] = [
                    'curso_id' => $curso->id,
                    'curso_requisito_id' => $curso->requisito_id
                ];

            endif;

            $data['escuela_curso'][] = [
                'escuela_id' => $curso->categoria_id,
                'curso_id' => $curso->id,
            ];

            $data['cursos'][] = [
                'external_id' => $curso->id,

                'name' => $curso->nombre,
                'description' => $curso->descripcion,
                'imagen' => $curso->imagen,

                'scheduled_restarts' => $curso->reinicios_programado,

                'freely_eligible' => $curso->libre,
                'assessable' => $curso->c_evaluable === 'si',

                'position' => $curso->orden,
                'active' => $curso->estado,
                'created_at' => $curso->created_at,
                'updated_at' => $curso->updated_at,
            ];
        }

        return $data;
    }

    protected function insertCursosData($data)
    {
        $this->makeChunkAndInsert($data['cursos'], 'courses');

        $schools = School::all();
        $courses = Course::all();

        $course_school = [];
        foreach ($data['escuela_curso'] as $relation) {
            $course = $courses->where('external_id', $relation['curso_id'])->first();
            $school = $schools->where('external_id', $relation['escuela_id'])->first();

            if ($course and $school)
                $course_school[] = ['course_id' => $course->id, 'school_id' => $school->id];

        }

        $this->makeChunkAndInsert($course_school, 'course_school');

//         TODO: Relaciones de curso requisito
        $course_requirements = [];
        foreach ($data['curso_requisitos'] as $relation) {
            $course = $courses->where('external_id', $relation['curso_id'])->first();
            $course_requirement = $courses->where('external_id', $relation['curso_requisito_id'])->first();

            if ($course and $course_requirement)
                $course_requirements[] = ['course_id' => $course->id, 'course_requirement_id' => $course_requirement->id];

        }

        $this->makeChunkAndInsert($course_requirements, 'course_requirements');
    }

    protected function setTemasData()
    {
        $db = self::connect();

        $temas = $db->getTable('posteos')
            ->select(
                'id', 'nombre', 'resumen', 'imagen', 'orden',
                'contenido', 'tipo_ev', 'evaluable', 'curso_id', 'requisito_id',
                'estado', 'created_at', 'updated_at')
            ->get();
        $courses = Course::all();
        $data = [];

        $topic_open_evaluations_type = Taxonomy::getFirstData('topic', 'evaluation-type', 'open');
        $topic_qualified_evaluations_type = Taxonomy::getFirstData('topic', 'evaluation-type', 'qualified');

        foreach ($temas as $tema) {
            $course = $courses->where('external_id', $tema->curso_id)->first();

            if ($course):
                if ($tema->requisito_id):

                    $data['tema_requisitos'][] = [
                        'tema_id' => $tema->id,
                        'tema_requisito_id' => $tema->requisito_id
                    ];

                endif;

                $type_evaluation_id = $tema->tipo_ev == 'abierta' ? $topic_open_evaluations_type->id
                    : ($tema->tipo_ev == 'calificada' ? $topic_qualified_evaluations_type->id : null);

                $data['temas'][] = [
                    'external_id' => $tema->id,
                    'course_id' => $course->id,

                    'name' => $tema->nombre,
                    'description' => $tema->resumen,
                    'content' => $tema->contenido,
                    'imagen' => $tema->imagen,

                    'assessable' => $tema->evaluable === 'si',

                    'type_evaluation_id' => $type_evaluation_id,

                    'position' => $tema->orden,
                    'active' => $tema->estado,

                    'created_at' => $tema->created_at,
                    'updated_at' => $tema->updated_at,
                ];
            endif;
        }

        return $data;
    }

    protected function insertTemasData($data)
    {
        $this->makeChunkAndInsert($data['temas'], 'topics');

        // TODO: Relaciones de tema requisito
        $topics = Topic::all();

        foreach ($data['tema_requisitos'] as $relation) {
            $topic = $topics->where('external_id', $relation['tema_id'])->first();
            $topic_requirement = $topics->where('external_id', $relation['tema_requisito_id'])->first();

            if ($topic and $topic_requirement)
                $topic->update(['topic_requirement_id' => $topic_requirement->id]);
        }
    }
}
