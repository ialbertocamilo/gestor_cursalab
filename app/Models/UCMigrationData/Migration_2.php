<?php

namespace App\Models\UCMigrationData;

use App\Models\Block;
use App\Models\CheckList;
use App\Models\Course;
use App\Models\CriterionValue;
use App\Models\Glossary;
use App\Models\Media;
use App\Models\MediaTema;
use App\Models\Poll;
use App\Models\School;
use App\Models\Support\OTFConnection;
use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vademecum;
use App\Models\Videoteca;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Video;

class Migration_2 extends Model
{
    const CHUNK_LENGTH = 5000;

    const MODULOS_EQUIVALENCIA = [
        4 => '26', // Mifarma
        5 => '27', // Inkafarma,
        6 => '28' // Capacitacion FP
    ];

    const MODULOS_CRITERION_VALUE = [
        4 => '18', // Mifarma
        5 => '19', // Inkafarma,
        6 => '20' // Capacitacion FP
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
            ->select('categorias.id as categoria_id', 'etapa', 'categorias.nombre')
            ->get();

        $bar = $output->createProgressBar($categorias->count());
        $output->info("UPDATE SCHOOLS NAME");
        $bar->start();

        foreach ($schools as $school) {
            $bar->advance();

            $categoria = $categorias->where('categoria_id', $school->external_id)->first();

            if ($categoria) {
                $name = "{$categoria->etapa} - {$categoria->nombre}";
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
            ->select('cursos.id as curso_id', 'etapa', 'cursos.nombre')
            ->get();

        $bar = $output->createProgressBar($cursos->count());
        $output->info("UPDATE COURSES NAME");
        $bar->start();

        foreach ($courses as $course) {
            $bar->advance();

            $curso = $cursos->where('curso_id', $course->external_id)->first();

            if ($curso) {
                $name = "{$curso->etapa} - {$curso->nombre}";
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

//        $media_topics = DB::table('media_topics')->get();
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
//                'value' => $media->valor,
                'value' => $valor,

                'type_id' => $media->tipo,
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

    protected function migrateCoursePolls($output)
    {
        $db = self::connect();

        $courses = Course::disableCache()
            ->whereNotNull('external_id')
            ->get();

        $polls = Poll::disableCache()
            ->whereNotNull('external_id')
            ->get();

        $curso_encuestas = $db->getTable('curso_encuesta')->get();
        $bar = $output->createProgressBar($curso_encuestas->count());
        $bar->start();

        $course_poll = [];

        foreach ($curso_encuestas as $curso_encuesta) {
            $bar->advance();

            $course = $courses->where('external_id', $curso_encuesta->curso_id)->first();
            $poll = $polls->where('external_id', $curso_encuesta->encuesta_id)->first();

            if (!$course || !$poll) {
                info("CURSO ID {$curso_encuesta->curso_id} o ENCUESTA ID {$curso_encuesta->encuesta_id} no existe en UC-IR.");
                continue;
            }

            $course_poll[] = ['course_id' => $course->id, 'poll_id' => $poll->id];
        }

        $bar->finish();

        $this->makeChunkAndInsert($course_poll, 'course_poll', $output);
    }

    protected function migrateChecklistData($output)
    {
        $db = self::connect();
        Taxonomy::firstOrCreate([
            'group' => 'checklist',
            'type' => 'type',
            'code' => 'user_trainer',
            'name' => 'Usuario a Entrenador',
            'active' => 1,
            'position' => 1,
        ]);
        Taxonomy::firstOrCreate([
            'group' => 'checklist',
            'type' => 'type',
            'code' => 'trainer_user',
            'name' => 'Entrenador a Usuario',
            'active' => 1,
            'position' => 2,
        ]);

        $checklist_items_type = Taxonomy::getData('checklist', 'type')->get();

        $UC_checklists = $db->getTable('checklist')->get();
        $UCChecklist_actividades = $db->getTable('checklist_items')->get();
        $checklist_curso = $db->getTable('relaciones_checklist')->get();

        $uc_workspace = $this->uc_workspace;

        $checklists_table = [];
        foreach ($UC_checklists as $checklist) {
            $checklists_table[] = [
                'external_id' => $checklist->id,
                'workspace_id' => $uc_workspace->id,
                'title' => $checklist->titulo,
                'description' => $checklist->descripcion,
                'active' => $checklist->estado,

                'created_at' => $checklist->created_at,
                'updated_at' => $checklist->updated_at,
            ];
        }
        $this->makeChunkAndInsert($checklists_table, 'checklists', $output);

        $checklists = CheckList::disableCache()->whereNotNull('external_id')->get();

        $type_equivalence = [
            'usuario_entrenador' => $checklist_items_type->where('code', 'user_trainer')->first()->id,
            'entrenador_usuario' => $checklist_items_type->where('code', 'trainer_user')->first()->id,
        ];

        foreach ($checklists as $checklist) {
            //actividades
            $actividades = $UCChecklist_actividades->where('checklist_id', $checklist->external_id);

            $temp = [];
            foreach ($actividades as $actividad) {
                $temp[] = [
                    'checklist_id' => $checklist->id,
                    'activity' => $actividad->actividad,
                    'position' => $actividad->posicion,
                    'type_id' => $type_equivalence[$actividad->tipo] ?? null,
                    'active' => $actividad->estado,

                    'created_at' => $actividad->created_at,
                    'updated_at' => $actividad->updated_at,
                ];
            }

            $this->makeChunkAndInsert($temp, 'checklist_items', $output);

            // courses
            $relaciones_checklist = $checklist_curso->where('checklist_id', $checklist->external_id);
            $courses_id = Course::disableCache()->whereIn('external_id', $relaciones_checklist->pluck('curso_id'))
                ->pluck('id')->toArray();

            $checklist->courses()->sync($courses_id);
        }

    }

    protected function migrateChecklistUserData($output)
    {
        $db = self::connect();

        // headers
        $checklist_rptas = $db->getTable('checklist_rptas')->get();
        $checklist_answers_items = [];

        $checklist_answers = [];
        foreach ($checklist_rptas as $checklist_rpta) {
            $checklist = CheckList::disableCache()->where('external_id', $checklist_rpta->checklist_id)->first();
            $coach = User::disableCache()->where('external_id', $checklist_rpta->entrenador_id)->first();
            $student = User::disableCache()->where('external_id', $checklist_rpta->alumno_id)->first();
            $course = Course::disableCache()->where('external_id', $checklist_rpta->curso_id)->first();
            $school = School::disableCache()->where('external_id', $checklist_rpta->categoria_id)->first();


            if (!$checklist || !$coach || !$student || !$course || !$school) {
                info("CHECKLIST :: {$checklist_rpta->checlist_id} - COACH :: {$checklist_rpta->entrenador_id} - STUDENT :: {$checklist_rpta->alumno_id} - SCHOOL :: {$checklist_rpta->categoria_id} -  CURSO :: {$checklist_rpta->curso_id}");
                continue;
            }

            $checklist_answer_data = [
                'checklist_id' => $checklist->id,
                'coach_id' => $coach->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
                'school_id' => $school->id,
                'percent' => $checklist_rpta->porcentaje,

                'created_at' => $checklist_rpta->created_at,
                'updated_at' => $checklist_rpta->updated_at,
            ];

            $checklist_answer_id = DB::table('checklist_answers')->insertGetId($checklist_answer_data);

            $checklist_rptas_items = $db->getTable('checklist_rptas_items')
                ->where('checklist_rpta_id', $checklist_rpta->id)->get();

            $checklist_answers_items_data = [];
            foreach ($checklist_rptas_items as $checklist_rpta_item) {

                $checklist_item_UC = $db->getTable('checklist_items')
                    ->where('id', $checklist_rpta_item->checklist_item_id)->first();

                if (!$checklist_item_UC) continue;

                $checklist_item = DB::table('checklist_items')->where('checklist_id', $checklist->id)
                    ->where('activity', $checklist_item_UC->actividad)->first();

                $checklist_answers_items_data[] = [
                    'checklist_answer_id' => $checklist_answer_id,
                    'checklist_item_id' => $checklist_item->id,
                    'qualification' => $checklist_rpta_item->calificacion,

                    'created_at' => $checklist_rpta_item->created_at,
                    'updated_at' => $checklist_rpta_item->updated_at,
                ];
            }

            $this->makeChunkAndInsert($checklist_answers_items_data, 'checklist_answers_items', $output);

        }


    }

    protected function migrateUserActionsData($output)
    {

        $db = self::connect();

        $usuario_acciones = $db->getTable('usuario_acciones')->get();
        $user_actions_data = [];

        $bar = $output->createProgressBar($usuario_acciones->count());
        $bar->start();

        foreach ($usuario_acciones as $usuario_accion) {
            $bar->advance();

            $model = null;

            if (str_contains($usuario_accion->model_type, 'Vademecum'))
                $model = Vademecum::class;

            if (str_contains($usuario_accion->model_type, 'Videoteca'))
                $model = Videoteca::class;

            if (!$model) {
                info("No es encontro el modelo {$usuario_accion->model_type}");
                continue;
            }

            $resource = $model::where('external_id', $usuario_accion->model_id)->first();

            $user = User::where('external_id', $usuario_accion->user_id)->first();

            if (!$user || !$resource) {
                info("Mo se encontro el USER :: {$usuario_accion->user_id} -  RESOURCE :: {$usuario_accion->model_id}");
                continue;
            }

            $user_actions_data[] = [
                'user_id' => $user->id,
                'model_type' => $model,
                'model_id' => $resource->id,
                'score' => $usuario_accion->score
            ];

        }

        $bar->finish();

        $this->makeChunkAndInsert($user_actions_data, 'user_actions', $output);
    }

    protected function migrateVideotecaData($output)
    {

        $db = self::connect();

        $videoteca_UC = $db->getTable('videoteca')->get();
        $bar = $output->createProgressBar($videoteca_UC->count());
        $bar->start();

        $videoteca_data = [];
        foreach ($videoteca_UC as $videoteca) {
            $bar->advance();

            $category = Taxonomy::where('external_id_es', $videoteca->category_id)->first();

            $media = Media::where('external_id', $videoteca->media_id)->first();
            $preview = Media::where('external_id', $videoteca->preview_id)->first();

            if (!$category) {
                info("La categoria :: {$videoteca->category_id} no se ha migrado");
                continue;
            }

            $videoteca_data[] = [

                'external_id' => $videoteca->id,

                'title' => $videoteca->title,
                'description' => $videoteca->description,
                'category_id' => $category->id,

                'media_video' => $videoteca->media_video,
                'media_type' => $videoteca->media_type,
                'media_id' => $media?->id,

                'preview_id' => $preview?->id,
                'active' => $videoteca->active,

                'created_at' => $videoteca->created_at,
                'updated_at' => $videoteca->updated_at,
            ];

        }
        $bar->finish();
        $this->makeChunkAndInsert($videoteca_data, 'videoteca', $output);

        $videotecaIR = Videoteca::disableCache()->whereNotNull('external_id') <> get();
        foreach ($videotecaIR as $videoteca) {

            $temp_modules = [];
            $videoteca_modulo = $db->getTable('videoteca_module')
                ->where('videoteca_id', $videoteca->external_id)
                ->get();
            foreach ($videoteca_modulo as $row) {
                $module_value = self::MODULOS_CRITERION_VALUE[$row->module_id] ?? false;

                if ($module_value) $temp_modules[] = $module_value;
            }

            $videoteca->modules()->sync($temp_modules);


            $temp_tags = [];
            $videoteca_tag = $db->getTable('videoteca_tag')
                ->where('videoteca_id', $videoteca->external_id)
                ->get();
            foreach ($videoteca_tag as $row) {
                $tag_value = Taxonomy::where('external_id_es', $row->tag_id)->first();

                if ($tag_value) $temp_tags[] = $tag_value->id;
            }

            $videoteca->tags()->sync($temp_tags);

        }
    }

    protected function migrateVademecumData($output)
    {

        $db = self::connect();

        $vademecumUC = $db->getTable('vademecum')->get();
        $bar = $output->createProgressBar($vademecumUC->count());
        $bar->start();

        $vademecum_data = [];
        foreach ($vademecumUC as $vademecum) {
            $bar->advance();

            $category = Taxonomy::where('external_id_es', $vademecum->categoria_id)->first();
            $subcategory = Taxonomy::where('external_id_es', $vademecum->subcategoria_id)->first();
            $media = Media::where('external_id', $vademecum->media_id)->first();

            if (!$category || !$media) {
                info("CATEGORIA :: {$vademecum->categoria_id} - MEDIA :: {$vademecum->media_id} no se ha migrado.");
                continue;
            }


            $vademecum_data[] = [

                'external_id' => $vademecum->id,

                'name' => $vademecum->nombre,
                'media_id' => $vademecum->media_id,
                'category_id' => $category->id,
                'subcategory_id' => $subcategory?->id,

                'active' => $vademecum->estado,

                'created_at' => $vademecum->created_at,
                'updated_at' => $vademecum->updated_at,
            ];
        }
        $bar->finish();

        $this->makeChunkAndInsert($vademecum_data, 'vademecum', $output);


        $vademecumIR = Vademecum::whereNotNull('external_id')->get();

        foreach ($vademecumIR as $vademecum) {


            $modules_values = $db->getTable('vademecum_modulo')
                ->where('vademecum_id', $vademecum->external_id)->get();

            $temp_modules = [];
            foreach ($modules_values as $row) {
                $module_value = self::MODULOS_CRITERION_VALUE[$row->modulo_id] ?? false;

                if ($module_value) $temp_modules[] = $module_value;
            }

            $vademecum->modules()->sync($temp_modules);
        }

    }


    protected function migrateGlosarioData($output)
    {
        $db = self::connect();

        $db->getTable('glosarios')
            ->chunkById(2500, function ($chunked) use ($output) {
            $bar = $output->createProgressBar($chunked->count());
            $bar->start();
            $glossary_data = [];

            foreach ($chunked as $glosario) {
                $bar->advance();

                $taxonomies = Taxonomy::whereIn('external_id_es', [
                    $glosario->categoria_id,
                    $glosario->jerarquia_id,
                    $glosario->laboratorio_id,
                    $glosario->condicion_de_venta_id,
                    $glosario->via_de_administracion_id,
                    $glosario->grupo_farmacologico_id,
                    $glosario->forma_farmaceutica_id,
                    $glosario->dosis_adulto_id,
                    $glosario->dosis_nino_id,
                    $glosario->recomendacion_de_administracion_id,
//                $glosario->contraindicacion_id,
//                $glosario->interacciones_frecuentes_id,
//                $glosario->reacciones_frecuentes_id,
                    $glosario->advertencias_id,
                ])->get();

                $categoria_id = $taxonomies->where('external_id_es', $glosario->categoria_id)->first();
                $laboratorio_id = $taxonomies->where('external_id_es', $glosario->laboratorio_id)->first();
                $condicion_de_venta_id = $taxonomies->where('external_id_es', $glosario->condicion_de_venta_id)->first();
                $via_de_administracion_id = $taxonomies->where('external_id_es', $glosario->via_de_administracion_id)->first();
                $jerarquia_id = $taxonomies->where('external_id_es', $glosario->jerarquia_id)->first();
                $grupo_farmacologico_id = $taxonomies->where('external_id_es', $glosario->grupo_farmacologico_id)->first();
                $forma_farmaceutica_id = $taxonomies->where('external_id_es', $glosario->forma_farmaceutica_id)->first();
                $dosis_adulto_id = $taxonomies->where('external_id_es', $glosario->dosis_adulto_id)->first();
                $dosis_nino_id = $taxonomies->where('external_id_es', $glosario->dosis_nino_id)->first();
                $recomendacion_de_administracion_id = $taxonomies->where('external_id_es', $glosario->recomendacion_de_administracion_id)->first();
                $advertencias_id = $taxonomies->where('external_id_es', $glosario->advertencias_id)->first();

//            $contraindicacion_id = $taxonomies->where('external_id_es', $glosario->contraindicacion_id)->first();
//            $interacciones_frecuentes_id = $taxonomies->where('external_id_es', $glosario->interacciones_frecuentes_id)->first();
//            $reacciones_frecuentes_id = $taxonomies->where('external_id_es', $glosario->reacciones_frecuentes_id)->first();

                $glossary_data[] = [
                    'external_id' => $glosario->id,

                    'name' => $glosario->nombre,

                    'categoria_id' => $categoria_id?->id,
                    'jerarquia_id' => $jerarquia_id?->id,
                    'laboratorio_id' => $laboratorio_id?->id,
                    'condicion_de_venta_id' => $condicion_de_venta_id?->id,
                    'via_de_administracion_id' => $via_de_administracion_id?->id,
                    'grupo_farmacologico_id' => $grupo_farmacologico_id?->id,
                    'forma_farmaceutica_id' => $forma_farmaceutica_id?->id,
                    'dosis_adulto_id' => $dosis_adulto_id?->id,
                    'dosis_nino_id' => $dosis_nino_id?->id,
                    'recomendacion_de_administracion_id' => $recomendacion_de_administracion_id?->id,
                    'advertencias_id' => $advertencias_id?->id,
//                'contraindicacion_id' => $contraindicacion_id?->id,
//                'interacciones_frecuentes_id' => $interacciones_frecuentes_id?->id,
//                'reacciones_frecuentes_id' => $reacciones_frecuentes_id?->id,

                    'active' => $glosario->estado,

                    'created_at' => $glosario->created_at,
                    'updated_at' => $glosario->updated_at,
                    'deleted_at' => $glosario->deleted_at,
                ];
            }

            $bar->finish();

            $this->makeChunkAndInsert($glossary_data, 'glossaries', $output);
        });

        $carrera_values = CriterionValue::with('parents')
            ->whereRelation('criterion', 'code', 'career')
            ->get();

        $glosario_taxonomia_UC = $db->getTable('glosario_taxonomia')->get();
        $glosario_modulo_UC = $db->getTable('glosario_modulo')->get();
        $carrera_glosario_categoria_UC = $db->getTable('carrera_glosario_categoria')
            ->join('carreras', 'carreras.id', 'carrera_glosario_categoria.carrera_id')
            ->get();

        $glossary_taxonomy_data = [];
        $glossary_module_data = [];
        $carrera_glosario_categoria_data = [];

        $bar = $output->createProgressBar($glosario_taxonomia_UC->count());
        $bar->start();
        foreach ($glosario_taxonomia_UC as $row) {
            $bar->advance();

            $glossary = Glossary::where('external_id', $row->glosario_id)->first();
            $taxonomy = Taxonomy::where('external_id_es', $row->taxonomia_id)->first();
            $glossary_group = Taxonomy::where('external_id_es', $row->glosario_grupo_id)->first();

            if (!$glossary || !$taxonomy || !$glossary_group) {
                info("GLOSARIO ID :: {$row->glosario_id} - TAXONOMY ID :: {$row->taxonomia_id} - GLOSSARY GROUP ID :: {$row->glosario_grupo_id} ");
                continue;
            }

            $glossary_taxonomy_data[] = [
                'glossary_id' => $glossary->id,
                'taxonomy_id' => $taxonomy->id,
                'glossary_group_id' => $glossary_group->id,
            ];

        }
        $bar->finish();
        $this->makeChunkAndInsert($glossary_taxonomy_data, 'glossary_taxonomy', $output);

        $bar = $output->createProgressBar($glosario_modulo_UC->count());
        $bar->start();
        foreach ($glosario_modulo_UC as $row) {
            $bar->advance();

            $glossary = Glossary::where('external_id', $row->glosario_id)->first();
            $module_id = self::MODULOS_EQUIVALENCIA[$row->modulo_id] ?? false;
            $code = $row->codigo;

            if (!$glossary || !$module_id) {
                info("GLOSARIO ID :: {$row->glosario_id} - MODULE ID :: {$row->modulo_id}");
                continue;
            }

            $glossary_module_data[] = [
                'glossary_id' => $glossary->id,
                'module_id' => $module_id,
                'code' => $code,
            ];

        }
        $bar->finish();
        $this->makeChunkAndInsert($glossary_module_data, 'glossary_module', $output);

        $bar = $output->createProgressBar($glosario_modulo_UC->count());
        $bar->start();
        foreach ($carrera_glosario_categoria_UC as $row) {
            $bar->advance();

            $carrera = $carrera_values->where('value_text', $row->nombre)->first();
            $glosario_categoria = Taxonomy::where('external_id_es', $row->glosario_categoria_id)->first();
            $module_id = self::MODULOS_EQUIVALENCIA[$row->config_id] ?? false;

            if (!$module_id || !$glosario_categoria || !$carrera) {
                info("carrera glossario categoria error => ");
                info($row->carrera_id);
                continue;
            }

            $carrera_glosario_categoria_data[] = [
                'module_id' => $module_id,
                'carrera_id' => $carrera->id,
                'glosario_categoria_id' => $glosario_categoria->id
            ];

        }
        $bar->finish();
        $this->makeChunkAndInsert($carrera_glosario_categoria_data, 'carrera_glosario_categoria', $output);
    }
}
