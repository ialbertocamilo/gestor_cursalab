<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Http\Controllers\ApiRest\RestAvanceController;

class MassiveUploadTopicGrades implements ToCollection
{
    /**
     * @param Collection $collection
     */

    public array $data = [];
    public array $no_procesados = [];
    public mixed $course_id;
    public mixed $topics = [];

    public function __construct($data)
    {
        $this->course_id = $data['course'];
        $this->topics = $data['topics'];
    }

    public function collection(Collection $excelData)
    {
        //   Comment para hacer merge
        $count = count($excelData);
        $course = Course::find($this->course_id);

        for ($i = 1; $i < $count; $i++) {
            $document_user = $excelData[$i][0];
            $grade = $excelData[$i][1];

            $user = User::where('dni', $document_user)->first();

            if (!$user) {
                $this->pushNoProcesados($excelData[$i], 'Usuario no existe');
                continue;
            }
            if (($course->assessable) && (count($this->topics) > 0 && $this->tipo_ev == 'calificada') && (empty($grade) || trim($grade) == "")) {
                $this->pushNoProcesados($excelData[$i], 'La nota está fuera del rango permitido');
                continue;
            }
            if (($course->assessable) && (count($this->topics) == 0) && (empty($grade) || trim($grade) == "")) {
                $this->pushNoProcesados($excelData[$i], 'La nota está fuera del rango permitido');
                continue;
            }
            if (($course->assessable) && (count($this->topics) > 0 && $this->tipo_ev == 'calificada') && ($grade < 0 || $grade > 20)) {
                $this->pushNoProcesados($excelData[$i], 'La nota está fuera del rango permitido');
                continue;
            }
            if (($course->assessable) && (count($this->topics) == 0) && ($grade < 0 || $grade > 20)) {
                $this->pushNoProcesados($excelData[$i], 'La nota está fuera del rango permitido');
                continue;
            }

            $assigned_courses = $user->getCurrentCourses();

            if (!in_array($this->course_id, $assigned_courses->toArray())) {
                $this->pushNoProcesados($excelData[$i], 'El curso seleccionado no está asignado para este usuario');
                continue;
            }

            $config = $this->getConfiguracionEvaluaciones();
            $topics = count($this->temas) > 0
                ? Topic::where('course_id', $this->course_id)->whereIn('id', $this->topics)->get()
                : Topic::where('course_id', $this->course_id)->get();

            $this->convalidarNotasPresenciale($config, $user, $topics, $grade);
        }
    }

    public function convalidarNotasPresenciale($config, $usuario, $temas, $nota)
    {
        $restAvanceController = new RestAvanceController();
        // dd($config);
        $nota_minima = $config['nota_aprobatoria'][0]['value'];
        $nro_intentos = $config['nro_intentos'];
        // Validar prueba existente por cada tema del curso
        $pruebas = Prueba::where('curso_id', $this->curso_id)->where('usuario_id', $usuario->id)->get();
        foreach ($temas as $key => $tema) {
            if ($tema->evaluable == 'si' && $tema->tipo_ev == 'calificada') {
                $prueba = $pruebas->where('posteo_id', $tema->id)->first();

                $puntaje = ($tema->tipo_cal && $tema->tipo_cal == 'CAL100') ? $nota * 5 : $nota;
                if ($prueba) {
                    if ($nota > $prueba->nota) {
                        // Actualizar la nota
                        $prueba->nota = $nota;
                        $prueba->puntaje = $puntaje;
                        $prueba->resultado = $nota < $nota_minima ? 0 : 1;
                        $prueba->fuente = 'manual';
                        $prueba->rptas_ok = json_encode([]);
                        $prueba->rptas_fail = json_encode([]);
                        $prueba->usu_rptas = json_encode([]);
                        $prueba->estado = 'terminado';
                        $prueba->last_ev = Carbon::now();
                        $prueba->save();
                    }
                    $visita = Visita::where('usuario_id', $usuario->id)->where('post_id', $tema->id)->first();
                    if (!$visita) {
                        $visita = new Visita();
                        $visita->curso_id = $this->curso_id;
                        $visita->post_id = $tema->id;
                        $visita->usuario_id = $usuario->id;
                        $visita->sumatoria = 1;
                        $visita->descargas = 0;
                    }
                    $visita->tipo_tema = 'calificada';
                    $visita->estado_tema = $nota < $nota_minima ? 'desaprobado' : 'aprobado';
                    $visita->save();
                } else {
                    $this->registrarPruebaPresencial($tema, $usuario->id, $nota, $puntaje, $nota_minima, $nro_intentos);
                }
            }
            if ($tema->evaluable == 'no') {
                $this->registarVisitaRevisado($tema, $usuario->id);
            }
        }
        $restAvanceController->actualizar_resumen_x_curso($usuario->id, $this->curso_id, $nro_intentos);
        $restAvanceController->actualizar_resumen_general($usuario->id);
    }

    public function excelDateToDate($fecha, $format, $rows = 0, $i = 0)
    {
        try {
            $php_date = $fecha - 25569;
            return date($format, strtotime("+$php_date days", mktime(0, 0, 0, 1, 1, 1970)));
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function registarVisitaRevisado($tema, $usuario_id)
    {
        $tipo_tema = 'no-evaluable';
        $estado_tema = 'revisado';
        $this->insertarVisita($usuario_id, $tema, $tipo_tema, $estado_tema);
    }

    public function registrarPruebaPresencial($tema, $usuario_id, $nota, $puntaje, $nota_minima, $nro_intentos)
    {
        /**
         * 1) Prueba
         * 2) Visitas
         * 3) Resumen x curso
         * 4) Resumen general
         */
        $prueba = Prueba::insert([
            'posteo_id' => $tema->id,
            'categoria_id' => $tema->categoria_id,
            'curso_id' => $tema->curso_id,
            'usuario_id' => $usuario_id,
            'usu_rptas' => json_encode([]),
            'rptas_fail' => json_encode([]),
            'rptas_ok' => json_encode([]),
            'nota' => $nota,
            'puntaje' => $puntaje,
            'estado' => 'terminado',
            'intentos' => 1,
            'resultado' => $nota < $nota_minima ? 0 : 1,
            'fuente' => 'manual',
            'last_ev' => Carbon::now(),
        ]);
        $tipo_tema = 'calificada';
        $estado_tema = $nota < $nota_minima ? 'desaprobado' : 'aprobado';
        $this->insertarVisita($usuario_id, $tema, $tipo_tema, $estado_tema);

    }

    public function insertarVisita($usuario_id, $tema, $tipo_tema, $estado_tema)
    {
        $visita = Visita::where('usuario_id', $usuario_id)->where('post_id', $tema->id)->first();
        if (!$visita) {
            $visita = new Visita();
            $visita->curso_id = $this->curso_id;
            $visita->post_id = $tema->id;
            $visita->usuario_id = $usuario_id;
            $visita->sumatoria = 1;
            $visita->descargas = 0;
        }
        $visita->tipo_tema = $tipo_tema;
        $visita->estado_tema = $estado_tema;
        $visita->save();
    }

    public function getConfiguracionEvaluaciones()
    {
        // Consultar nota minima en curso
        $curso_eva = Curso::where('id', $this->curso_id)->first();
        // $config = Abconfig::select('mod_evaluaciones')->where('id', $curso_eva->config_id)->first();
        // $mod_eval = json_decode($config->mod_evaluaciones, true);
        // //verificando si existe mod_evalucion en curso
        if ($curso_eva->mod_evaluaciones != null) {
            $mod_eval = json_decode($curso_eva->mod_evaluaciones, true);
        }
        return $mod_eval;
    }

    public function pushNoProcesados($excelRaw, $info)
    {
        // $hora = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelRaw[3])->format('H:i');
        $this->no_procesados[] = [
            'dni' => $excelRaw[0],
            'nota' => $excelRaw[1],
            // 'last_ev' => $excelRaw[2],
            // 'hora' => $hora,
            'info' => $info
        ];
    }

    public function getNoProcesados()
    {
        return $this->no_procesados;
    }
}
