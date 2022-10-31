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

    const MODULOS_EQUIVALENCIA = [
        4 => '26', // Mifarma
        5 => '27', // Inkafarma,
        6 => '28' // Capacitacion FP
    ];

    private mixed $uc_workspace;

    public function __construct()
    {
        $this->uc_workspace = Workspace::where('slug', "farmacias-peruanas")->first();
        $this->db = self::connect();
    }

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    public function insertChunkedData($data, $table_name, $output)
    {
        $output->info($table_name);

        $bar = $output->createProgressBar(count($data));
        $bar->start();

        foreach ($data as $chunk) {
            $bar->advance();
            DB::table($table_name)->insert($chunk);
        }

        $bar->finish();
        $output->newLine();

    }

    public function makeChunkAndInsert($data, $table_name, $output)
    {
        $chunk = array_chunk($data, self::CHUNK_LENGTH, true);

        $this->insertChunkedData($chunk, $table_name, $output);
    }

    protected function migrateEscuelas($output)
    {
        $escuelas_data = self::setEscuelasData($output);

        self::insertEscuelasData($escuelas_data, $output);
    }

    protected function migrateEscuelasNombre($output)
    {

        $db = self::connect();

        $schools = School::disableCache()->whereNotNull('external_id')->get();
        $categorias = $db->getTable('categorias')
            ->join('ab_config', 'ab_config.id', 'categorias.config_id')
            ->select('categorias.id as categoria_id', 'etapa')
            ->get();

        $bar = $output->createProgressBar($categorias->count());
        $output->info("UPDATE SCHOOLS NAME");
        $bar->start();

        foreach ($schools as $school) {
            $bar->advance();

            $categoria = $categorias->where('categoria_id', $school->external_id)->first();

            if ($categoria) {
                $name = "{$categoria->etapa} - {$school->name}";
                $school->update(['name' => $name]);
            }
        }

        $bar->finish();
        $output->newLine();
    }

    protected function migrateCursosNombre($output)
    {
        $db = self::connect();

        $courses = Course::disableCache()->whereNotNull('external_id')->get();
        $cursos = $db->getTable('cursos')
            ->join('ab_config', 'ab_config.id', 'cursos.config_id')
            ->select('cursos.id as curso_id', 'etapa')
            ->get();

        $bar = $output->createProgressBar($cursos->count());
        $output->info("UPDATE COURSES NAME");
        $bar->start();

        foreach ($courses as $course) {
            $bar->advance();

            $curso = $cursos->where('curso_id', $course->external_id)->first();

            if ($curso) {
                $name = "{$curso->etapa} - {$course->name}";
                $course->update(['name' => $name]);
            }
        }

        $bar->finish();
        $output->newLine();
    }

    protected function migrateCursos($output)
    {
        $cursos_data = self::setCursosData($output);

        self::insertCursosData($cursos_data, $output);
    }

    protected function migrateTemas($output)
    {
        $temas_data = self::setTemasData($output);

        self::insertTemasData($temas_data, $output);
    }

    protected function migrateMediaTopics($output)
    {
        $db = self::connect();

        $media_temas = $db->getTable('media_temas')
//            ->whereIn('tipo', ['audio', 'scorm'])
            ->get();

        $topics = Topic::disableCache()->whereNotNull('external_id')->select('id', 'external_id')->get();
        $data = [];
        $bar = $output->createProgressBar($media_temas->count());
        $bar->start();

        foreach ($media_temas as $media) {
            $bar->advance();

            $topic = $topics->where('external_id', $media->tema_id)->first();

            if (!$topic) {
                info("[migrateMediaTopics] No se encuentra el media_tema_id-{$media->tema_id} en los temas migrados");
                continue;
            }

            $valor = $media->valor;

            if ($media->tipo === 'scorm') {
                $valor = str_replace('https://gestor.universidadcorporativafp.com.pe/', 'https://gestiona.inretail.cursalab.io/', $valor);
            }

            if ($media->tipo === 'audio') {
                $bucket_base_url = 'https://cursalabio.s3.sa-east-1.amazonaws.com/';
                $valor = str_replace('https://ucfp.sfo2.cdn.digitaloceanspaces.com/', $bucket_base_url, $valor);
            }

            $data[] = [
                'topic_id' => $topic->id,

                'title' => $media->titulo,
                'value' => $media->valor,

                'type_id' => $valor,
//                'type_id' => $media->tipo,
                'embed' => $media->embed,
                'downloadable' => $media->descarga,
                'position' => $media->orden,

                'created_at' => $media->created_at,
                'updated_at' => $media->updated_at,
            ];
        }

        $bar->finish();
        $output->newLine();

        $this->makeChunkAndInsert($data, 'media_topics', $output);
    }

    protected function insertMultimedia($output)
    {
        $db = $this->connect();
        $multimedia = $db->getTable('media')
            ->select()
            ->get();
        $medias = [];
        $bar = $output->createProgressBar($multimedia->count());
        $bar->start();

        foreach ($multimedia as $media) {
            $bar->advance();

            $valor = $media->file;

            if ($media->ext === 'scorm') {
                $valor = str_replace('https://gestor.universidadcorporativafp.com.pe/', 'https://gestiona.inretail.cursalab.io/', $valor);
            }

            if ($media->ext === 'audio') {
                $bucket_base_url = 'https://cursalabio.s3.sa-east-1.amazonaws.com/';
                $valor = str_replace('https://ucfp.sfo2.cdn.digitaloceanspaces.com/', $bucket_base_url, $valor);
            }

            $medias[] = [
                'external_id' => $media->id,
                'title' => $media->title,
                'description' => $media->description,
//                'file' => $media->file,
                'file' => $valor,
                'ext' => $media->ext,

                'created_at' => $media->created_at,
                'updated_at' => $media->updated_at,
            ];
        }

        $bar->finish();
        $output->newLine();

        $this->makeChunkAndInsert($medias, 'media', $output);
    }

    protected function migrateCurricula()
    {

        self::insertCurriculaData();
    }

    protected function setEscuelasData($output)
    {
        $db = self::connect();

        $categorias = $db->getTable('categorias')
            ->select(
                'id', 'config_id', 'nombre', 'descripcion', 'imagen', 'orden',
                'plantilla_diploma', 'reinicios_programado',
                'estado', 'created_at', 'updated_at')
            ->get();
        $data = [];

        $bar = $output->createProgressBar($categorias->count());
        $bar->start();

        foreach ($categorias as $escuela) {
            $bar->advance();
            $name = "M{$escuela->config_id}-$escuela->nombre";

            $data['schools'][] = [
                'external_id' => $escuela->id,

                'name' => $name,
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
        $bar->finish();
        $output->newLine();

        return $data;
    }

    protected function insertEscuelasData($data, $output)
    {
        $this->makeChunkAndInsert($data['schools'] ?? [], 'schools', $output);

        $uc_workspace = $this->uc_workspace;
        $schools = School::disableCache()->whereNotNull('external_id')->get();
        $school_workspace = [];

        foreach ($schools as $school)
            $school_workspace[] = ['school_id' => $school->id, 'workspace_id' => $uc_workspace->id];

        $this->makeChunkAndInsert($school_workspace, 'school_workspace', $output);
    }

    protected function setCursosData($output)
    {
        $db = self::connect();

        $cursos = $db->getTable('cursos')
            ->select(
                'id', 'config_id', 'nombre', 'descripcion', 'imagen', 'orden',
                'plantilla_diploma', 'reinicios_programado', 'c_evaluable', 'libre',
                'categoria_id', 'requisito_id',
                'estado', 'created_at', 'updated_at')
            ->get();
        $data = [];
        $bar = $output->createProgressBar($cursos->count());
        $bar->start();
        foreach ($cursos as $curso) {
            $bar->advance();

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
        $bar->finish();
        $output->newLine();

        return $data;
    }

    protected function insertCursosData($data, $output)
    {
        $this->makeChunkAndInsert($data['cursos'] ?? [], 'courses', $output);

        $schools = School::disableCache()->whereNotNull('external_id')->select('id', 'external_id')->get();
        $courses = Course::disableCache()->whereNotNull('external_id')->select('id', 'external_id')->get();

        $course_school = [];
        foreach ($data['escuela_curso'] ?? [] as $relation) {
            $course = $courses->where('external_id', $relation['curso_id'])->first();
            $school = $schools->where('external_id', $relation['escuela_id'])->first();

            if ($course and $school)
                $course_school[] = ['course_id' => $course->id, 'school_id' => $school->id];

        }
        $this->makeChunkAndInsert($course_school, 'course_school', $output);


        $course_requirements = [];
        foreach ($data['curso_requisitos'] ?? [] as $relation) {
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
        $this->makeChunkAndInsert($course_requirements, 'requirements', $output);


        $uc_workspace = $this->uc_workspace;
        $course_workspace = [];
        foreach ($courses as $course) {
            $course_workspace[] = [
                'workspace_id' => $uc_workspace->id,
                'course_id' => $course->id,
            ];
        }
        $this->makeChunkAndInsert($course_workspace, 'course_workspace', $output);
    }

    protected function setTemasData($output)
    {
        $db = self::connect();

        $temas = $db->getTable('posteos')
            ->select(
                'id', 'nombre', 'resumen', 'imagen', 'orden',
                'contenido', 'tipo_ev', 'evaluable', 'curso_id', 'requisito_id',
                'estado', 'created_at', 'updated_at')
            ->get();
        $courses = Course::disableCache()->select('id', 'external_id')->get();
        $data = [];

        $topic_open_evaluations_type = Taxonomy::getFirstData('topic', 'evaluation-type', 'open');
        $topic_qualified_evaluations_type = Taxonomy::getFirstData('topic', 'evaluation-type', 'qualified');

        $bar = $output->createProgressBar($temas->count());
        $bar->start();
        foreach ($temas as $tema) {
            $bar->advance();

            $course = $courses->where('external_id', $tema->curso_id)->first();

            if ($course):
                if ($tema->requisito_id):

                    $data['tema_requisitos'][] = [
                        'tema_id' => $tema->id,
                        'tema_requisito_id' => $tema->requisito_id,
                        'created_at' => $tema->created_at,
                        'updated_at' => $tema->updated_at,
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
        $bar->finish();
        $output->newLine();

        return $data;
    }

    protected function insertTemasData($data, $output)
    {
        $this->makeChunkAndInsert($data['temas'], 'topics', $output);

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
                    'requirement_id' => $topic_requirement->id,
                    'created_at' => $relation['created_at'],
                    'updated_at' => $relation['updated_at'],
                ];
            }
        }

        $this->makeChunkAndInsert($topic_requirements, 'requirements', $output);
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
