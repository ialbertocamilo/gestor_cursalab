<?php

namespace App\Imports;

use App\Models\AssignedRole;
use App\Models\ChecklistRpta;
use App\Models\PushNotification;
use App\Models\Usuario;
use App\Models\EntrenadorUsuario;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class AsignarEntrenadorImport implements ToCollection
{
    public $data_ok;
    public $data_no_procesada;
    public $msg;

    public function collection(Collection $data)
    {
        $data_ok = collect();
        $data_no_procesada = collect();
        $excelData = collect();
        // Remover cabeceras : DNI ENTRENADOR, DNI USUARIO y ESTADO
        $filtered = $data->reject(function ($value, $key) {
            return $key == 0;
        });
        // Extraer toda la data del excel
        $filtered->each(function ($value, $key) use ($excelData) {
            if (!empty($value[0]) && !empty($value[1])) {
                $temp = [
                    'entrenador_dni' => $value[0],
                    'usuario_dni' => $value[1],
                    'estado' => 1
                ];
                $excelData->push($temp);
            }
        });
        // Agrupar por entrenador DNI
        $agrupados = $excelData->groupBy(function ($item, $key) {
            return $item['entrenador_dni'];
        });

        foreach ($agrupados->all() as $entrenador_dni => $alumnos) {
            if (empty($entrenador_dni)) {
                continue;
            }
            $entrenador = User::where('document', $entrenador_dni)->where('active', 1)->first();
            if (!$entrenador) {
                $temp = [
                    'dni' => $entrenador_dni,
                    'nombre' => null,
                    'msg' => 'El entrenador no existe'
                ];
                $data_no_procesada->push($temp);
                continue;
            }

            $is_student = EntrenadorUsuario::alumno($entrenador->id)->first();
            if (!is_null($is_student)) {
                $temp = [
                    'dni' => (string)$entrenador_dni,
                    'nombre' => $entrenador->name,
                    'msg' => 'El entrenador es un alumno.'
                ];
                $data_no_procesada->push($temp);
                continue;
            }
            $entrenador_workspace = Workspace::select('parent_id')->where('id', $entrenador->subworkspace_id)->first();
            if (is_null($entrenador_workspace)) {
                $entrenador_workspace = AssignedRole::getUserAssignedRoles($entrenador->id)->first();
                $entrenador_workspace_id = $entrenador_workspace->scope;
            } else {
                $entrenador_workspace_id = $entrenador_workspace->parent_id;
            }
            $subworkspaces = Workspace::where('parent_id', $entrenador_workspace_id)->pluck('id')->all();

            $alumnos = collect($alumnos);
            $usuarios = User::whereIn('document', $alumnos->pluck('usuario_dni')->all())
                ->whereIn('subworkspace_id', $subworkspaces)
                ->where('active', 1)
                ->get();

            // Usuarios DNI no existen o no pertenecen al módulo del entrenador
            $usuarios_dni_error = $alumnos->pluck('usuario_dni')->diff($usuarios->pluck('document'));
            $usuarios_dni_error->each(function ($value, $key) use ($data_no_procesada, $entrenador, $subworkspaces) {
                $tempUsuario = User::where('document', $value)->first();
                // VALIDACIONES DNI MENOR A 8 DIGITOS
                if (strlen($value) < 8) {
                    $msg = 'El DNI no cumple con el formato requerido (8 dígitos como mínimo).';
                } else if (!$tempUsuario) {
                    $msg = "El DNI no pertenece a un usuario del gestor ";
                } else if ($tempUsuario->active === 0) {
                    $msg = "Alumno se encuentra inactivo en el gestor.";
                } else if (!in_array($tempUsuario->subworkspace_id, $subworkspaces)) {
                    $msg = "Alumno no pertenece al mismo módulo del entrenador.";
                } else {
                    $msg = '';
                }
                $temp = ['dni' => (string)$value, 'nombre' => null, 'msg' => $msg];
                $data_no_procesada->push($temp);
            });

            // SI NO SE ENCUENTRA NINGUNO ALUMNO CON LA DATA CORRECTA SE PASA AL SIGUIENTE ENTRENADOR
            if ($usuarios->count() === 0) {
                continue;
            }
            // SI NO SE ENCUENTRA NINGUNO ALUMNO CON LA DATA CORRECTA SE PASA AL SIGUIENTE ENTRENADOR


            // if ($entrenador->rol_entrenamiento === null) {
            //     $entrenador->rol_entrenamiento = 'entrenador';
            //     $entrenador->save();
            // }
            foreach ($usuarios as $alumno) {
                $nuevo_entrenador_id = $entrenador->id;
                $alumnoData = User::where('document', $alumno->document)->first();
                $is_coach = EntrenadorUsuario::entrenador($alumnoData->id)->first();
                if (!is_null($is_coach)) {
                    $temp = [
                        'dni' => $alumno->document,
                        'nombre' => $alumnoData->name,
                        'msg' => 'El alumno es un entrenador.'
                    ];
                    $data_no_procesada->push($temp);
                    continue;
                }
                // if ($alumnoData->rol_entrenamiento === null) {
                //     $alumnoData->rol_entrenamiento = 'alumno';
                //     $alumnoData->save();
                // }

                $dataUsuarioExcel = $alumnos->where('usuario_dni', $alumno->document)->first();
                $dataTempUsuario = [
                    'trainer_id' => $entrenador->id,
                    'user_id' => $alumno->id,
                    'active' => $dataUsuarioExcel['estado']
                ];

                // // TODO: verificar si tiene un entrenador actual (estado = 1)
                // $relacion_entrenamiento_activa = EntrenadorUsuario::alumno($alumno->id)->estado(1)->first();
                // if ($relacion_entrenamiento_activa) {
                //     if ((int)$relacion_entrenamiento_activa->entrenador_id !== (int)$nuevo_entrenador_id) {
                //         // Inactivar relacion con el actual entrenador
                //         $relacion_entrenamiento_activa->estado = 0;
                //         $relacion_entrenamiento_activa->save();
                //         /*// Crear o activar la relacion con el nuevo entrenador indicado en el excel
                //         EntrenadorUsuario::updateOrCreate(
                //             ['entrenador_id' => $nuevo_entrenador_id, 'usuario_id' => $alumno->id],
                //             ['estado' => 1, 'estado_notificacion' => 0]
                //         );*/
                //     }
                // }

                $registro = EntrenadorUsuario::asignar($dataTempUsuario);
                $alumno->msg = $registro['msg'];
                if (!$registro['error'])
                    $data_ok->push($alumno->only('document', 'name', 'msg'));
                else
                    $data_no_procesada->push($alumno->only('document', 'name', 'msg'));

                // TODO: Validar si el alumno tiene checklists incompletos (porcentaje < 100.00)
                // TODO: Si tiene checklist incompletos actualizar el campo entrenador_id si el nuevo entrenador y el antiguo entrenador son diferentes, sino no

                // $checklists_incompletos = ChecklistRpta::where('alumno_id', $alumno->id)->where('porcentaje', '<', 100.00)->get();
                // info($checklists_incompletos);
                // if ($checklists_incompletos && $relacion_entrenamiento_activa) {
                //     if ($checklists_incompletos->count() > 0) {
                //         if ((int)$nuevo_entrenador_id !== (int)$relacion_entrenamiento_activa->entrenador_id) {
                //             ChecklistRpta::where('alumno_id', $alumno->id)->where('porcentaje', '<', 100.00)->update(
                //                 ['entrenador_id' => $nuevo_entrenador_id]
                //             );
                //         }
                //     }
                // }

                //TODO: Enviar notificación al alumno
                //                NotificacionPush::enviar('titulo', 'texto', [$alumnoData->token_firebase], []);
            }
            //TODO: Enviar notificación al entrenador
            //            NotificacionPush::enviar('titulo', 'texto', [$entrenador->token_firebase], []);
        }

        $this->msg = 'Excel procesado.';
        $this->data_no_procesada = $data_no_procesada;
        $this->data_ok = $data_ok;
    }

    public function get_data()
    {
        return [
            'msg' => $this->msg,
            'data_ok' => $this->data_ok,
            'data_no_procesada' => $this->data_no_procesada
        ];
    }
}
