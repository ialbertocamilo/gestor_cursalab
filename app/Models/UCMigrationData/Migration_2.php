<?php

namespace App\Models\UCMigrationData;

use App\Models\Block;
use App\Models\Course;
use App\Models\CriterionValue;
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
        $temas_data = self::setTemasData();

        self::insertTemasData($temas_data);
    }

    protected function migrateCurricula()
    {

        self::insertCurriculaData();
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

        $course_requirements = [];
        foreach ($data['curso_requisitos'] as $relation) {
            $course = $courses->where('external_id', $relation['curso_id'])->first();
            $course_requirement = $courses->where('external_id', $relation['curso_requisito_id'])->first();

            if ($course and $course_requirement) {
                $course_requirements[] = [
                    'model_type' => Course::class,
                    'model_id' => $course->id,
                    'requirement_type' => Course::class,
                    'requirement_id' => $course_requirement->id
                ];
            }
        }
        $this->makeChunkAndInsert($course_requirements, 'requirements');

        $uc_workspace = Workspace::where('slug', 'universidad-corporativa')->first();
        $courses = Course::all();
        $course_workspace = [];

        foreach ($courses as $course) {
            $course_workspace[] = [
                'workspace_id' => $uc_workspace->id,
                'course_id' => $course->id,
            ];
        }

        $this->makeChunkAndInsert($course_workspace, 'course_workspace');
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

        $topics = Topic::all();
        $topic_requirements = [];

        foreach ($data['tema_requisitos'] as $relation) {
            $topic = $topics->where('external_id', $relation['tema_id'])->first();
            $topic_requirement = $topics->where('external_id', $relation['tema_requisito_id'])->first();

            if ($topic and $topic_requirement) {
                $topic_requirements[] = [
                    'model_type' => Topic::class,
                    'model_id' => $topic->id,
                    'requirement_type' => Topic::class,
                    'requirement_id' => $topic_requirement->id
                ];
            }
        }
        $this->makeChunkAndInsert($topic_requirements, 'requirements');
    }

    public function insertCurriculaData()
    {
        $db = self::connect();

        $all_courses = Course::all();
        $carreras_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();
//        $ciclos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'ciclo'))->get();
        $curriculas = $db->getTable('curricula')
            ->join('ciclos', 'ciclos.id', '=', 'curricula.ciclo_id')
            ->select('curricula.carrera_id', 'ciclo_id', 'curso_id', 'ciclos.nombre')->get();
        $programs = Block::with(
            'segments.values.criterion_value',
            'block_children.child.segments.values.criterion_value')
            ->whereHas('block_children')->get();

        foreach ($programs as $program) {
            $program_criterion_values = collect();
            $program->segments->each(function ($segment) use ($program_criterion_values) {
                $segment->values->each(function ($value) use ($program_criterion_values) {
                    $program_criterion_values->push($value->criterion_value->id);
                });
            });
//            info("# RUTAS DEL PROGRAMA " . $program->block_children->count());
            foreach ($program->block_children as $block_child) {
                $block_child_criterion_values = collect();
                $block_child->child->segments->each(function ($segment) use ($block_child_criterion_values) {
                    $segment->values->each(function ($value) use ($block_child_criterion_values) {
                        $block_child_criterion_values->push($value->criterion_value->value_text);
                    });
                });

                $career = $carreras_values->where('id', $program_criterion_values->first())->first();
                $ciclo_value_text = $block_child_criterion_values->first();

                $cursos_curriculas = $curriculas->where('carrera_id', $career->external_id)
                    ->where('nombre', $ciclo_value_text);
//                info("  # CURRICULAS ENCONTRADAS " . $cursos_curriculas->count());
                $courses = $all_courses->whereIn('external_id', $cursos_curriculas->pluck('curso_id'));
//                info("      # CURSOS DEL PROGRAMA " . $courses->count());

                $block_child->child->courses()->syncWithoutDetaching($courses->pluck('id'));
//                dd($program_criterion_values->first(), $block_child_criterion_values->first());
//                dd($block_child_criterion_values->first());
//                dd($career, $ciclo);
//                dd($program->name, $block_child->child->name);
//                dd($career->external_id, $ciclo->external_id);
//                dd($cursos_curriculas->pluck('curso_id')->count());
//                dd($courses->pluck('id')->count());
//                dd($program_criterion_values->toArray(), $block_child_criterion_values->toArray(), $courses->pluck('id')->toArray());
            }
        }
    }
}
