<?php

namespace App\Models\Massive;

use App\Models\User;
use App\Models\Massive;
use App\Models\Workspace;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToCollection;

class ChangeStateUserMassive extends Massive implements ToCollection
{
    public $q_change_status = 0;
    public $q_errors = 0;
    public $errors = [];
    // public $identifier = '';
    public $messageInSpanish = true;
    public $identificator;// declare variable when set the instance of this class - The values ​​must be user attributes.
    public $state_user_massive; //declare variable when set the instance of this class

    public $rows = [];
    public $error_message = null;
    public $rows_to_activate = 0;
    public $subworkspaces = [];
    public function __construct($data = [])
    {
        $this->name_socket = $this->formatNameSocket('upload-massive', $data['number_socket'] ?? null);
        $this->percent_sent = [];
    }

    public function collection(Collection $rows)
    {
        //Don't count the header in the constraint, verifyConstraintMassive <- function extends from class Massive
        $this->verifyConstraintMassive('change_status_massive', count($rows) - 1);

        // state_user_massive <- 1(active) or 0(inactive)
        //Delete header
        $rows->shift();
        $this->rows = $rows;
        $current_workspace = get_current_workspace();

        if ($this->state_user_massive == 1 && !$this->validateLimitAllowedUsers()):
            $message = config('errors.limit-errors.limit-user-allowed');
            $current_workspace->sendEmailByLimit();
            $this->error_message = $message;
            return;
        endif;
        // $current_workspace= get_current_workspace();
        $this->subworkspaces = Workspace::select('id')->where('parent_id',$current_workspace->id)->get()?->pluck('id')->toArray();
        // process dni or email <- change state user where dni or email
        $this->processData($rows);
        $this->q_errors = count($this->errors);
    }

    private function validateLimitAllowedUsers(): bool
    {
        $current_workspace = get_current_workspace();
        $workspace_limit = $current_workspace->getLimitAllowedUsers();
        if (!$workspace_limit) return true;

        $documents_to_activate = $this->rows->pluck(0)->toArray();
//        dd($documents_to_activate);

        $users_to_activate_count = User::onlyClientUsers()
            ->whereIn('document', $documents_to_activate)
            ->whereRelation('subworkspace', 'parent_id', $current_workspace->id)
            ->where('active', INACTIVE)
            ->count();

        $this->rows_to_activate = $users_to_activate_count;

        $users_active_count = User::onlyClientUsers()
            ->whereRelation('subworkspace', 'parent_id', $current_workspace->id)
            ->where('active', ACTIVE)
            ->count();


//        dd($users_active_count, $users_to_activate_count, $workspace_limit);
//        dd(($users_active_count + $users_to_activate_count) <= $workspace_limit);
        return ($users_active_count + $users_to_activate_count) <= $workspace_limit;
    }

    private function processData($rows)
    {
        $count_users = count($rows);
        $counter = 0;
        foreach ($rows->toArray() as $row) {
            $percent = round(($counter / $count_users) * 100);
            $this->sendEchoPercentEvent($percent, $this->name_socket, $this->percent_sent) && $this->percent_sent[] = $percent;
            $counter++;
            if (!is_null($row)) {
                $user_identifier = is_array($row) ? $row[0] : $row;
                $termination_date = is_array($row) ? $row[1] : null;
                if ($termination_date) {
                    $termination_date = $this->excelDateToDate($termination_date);
                    if ($termination_date == 'invalid date') {
                        $message = $this->messageInSpanish ? 'Fecha inválida. (YYYY-mm-dd)' : 'Invalid date. (YYYY-mm-dd)';
                        $this->setError($user_identifier, $row[1], $message);
                        continue;
                    }
                }
                if ($user_identifier) {

//                    $current_workspace = get_current_workspace();
//
//                    if ($this->state_user_massive && !$current_workspace->verifyLimitAllowedUsers()):
//                        $this->users_not_activated_by_limit = $count_users - $this->q_change_status;
//                        break;
//                    endif;

                    $this->changeStatusUser($user_identifier, $termination_date, $this->identificator);
                }
            }
        }
    }

    private function changeStatusUser($user_identifier, $termination_date, $identificator)
    {
        $user = User::where(trim($identificator), $user_identifier)->first();
        if ($user) {
            if(!in_array($user->subworkspace_id,$this->subworkspaces)){
                $this->setError($user_identifier, $termination_date, 'Este usuario no pertenece a tu workspace');
            }else{
                $user->updateStatusUser($this->state_user_massive, $termination_date,true);
                $this->q_change_status++;
            }
        } else {
            //ERRORES
            $message = $this->messageInSpanish ? 'Usuario no encontrado.' : 'Resource not found.';
            $this->setError($user_identifier, $termination_date, $message);
        }
    }

    public function getStaticHeader($header_status_active = true, $add_observations = false)
    {
        $headers = $header_status_active ? collect(['DNI']) : collect(['DNI', 'FECHA DE CESE (OPCIONAL)']);
        $add_observations && $headers = $headers->merge('Observaciones');
        return $headers;
    }

    private function setError($user_identifier, $termination_date, $message)
    {
        // ,'message'=>'Resource not found.'
        $error = ['value' => $user_identifier];
        (!$this->state_user_massive) && $error['termination_date'] = $termination_date;
        $error['message'] = $message;
        $this->errors[] = $error;
    }

    private function excelDateToDate($fecha)
    {
        try {
            if (_validateDate($fecha, 'Y-m-d')) {
                return $fecha;
            }
            if (_validateDate($fecha, 'Y/m/d') || _validateDate($fecha, 'd/m/Y') || _validateDate($fecha, 'd-m-Y')) {
                // return date("d/m/Y",$fecha);
                return Carbon::parse($fecha)->format('Y-m-d');
            }
            $php_date = $fecha - 25569;
            $date = date("Y-m-d", strtotime("+$php_date days", mktime(0, 0, 0, 1, 1, 1970)));
            return $date;
        } catch (\Throwable $th) {
            return 'invalid date';
        }
    }
}
