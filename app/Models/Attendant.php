<?php

namespace App\Models;

use App\Http\Resources\Meeting\MeetingSearchAttendantsResource;
use App\Http\Resources\Meeting\MeetingShowAttendantsMeetingResource;
use App\Imports\Meeting\AttendantsFormImport;
use App\Services\ZoomService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use function foo\func;

class Attendant extends BaseModel
{
    protected $fillable = [
        'meeting_id', 'usuario_id',
        'link', 'total_logins', 'total_logouts', 'total_duration',
        'first_attempt_at', 'first_login_at', 'first_logout_at', 'last_login_at', 'last_logout_at',
        'identifier', 'online', 'ip', 'confirmed_attendance_at', 'type_id',
        'present_at_first_call', 'present_at_middle_call', 'present_at_last_call',
        'browser_family_id', 'browser_version_id', 'platform_family_id', 'platform_version_id', 'device_family_id', 'device_model_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function device_family()
    {
        return $this->belongsTo(Taxonomy::class, 'device_family_id');
    }

    public function browser_family()
    {
        return $this->belongsTo(Taxonomy::class, 'browser_family_id');
    }

    public function platform_family()
    {
        return $this->belongsTo(Taxonomy::class, 'platform_family_id');
    }

    public function device_model()
    {
        return $this->belongsTo(Taxonomy::class, 'device_model_id');
    }

    public function browser_version()
    {
        return $this->belongsTo(Taxonomy::class, 'browser_version_id');
    }

    public function platform_version()
    {
        return $this->belongsTo(Taxonomy::class, 'platform_version_id');
    }

    public function getTotalDurationPercentInMeeting()
    {
        $this->load('meeting');

        $meeting_total_duration = $this->meeting->getTotalDuration();
        if ($meeting_total_duration > 0)
            return number_format($this->total_duration / $meeting_total_duration, 2, '.', ',') * 100;

        return 0;
    }

    protected function getMeetingAttendants($data)
    {
        $attendants = self::with(['usuario.config:id,etapa,logo', 'type',
            'usuario.matricula_presente.carrera',
            'meeting' => function ($q) use ($data) {
                $q->betweenScheduleDates($data);
                $q->ofReservedStatus();
                $q->excludeMeeting($data['meeting_id'] ?? null);
            }])
            ->when($data['exclude_type_id'] ?? NULL, function ($q) use ($data) {
                $q->where('type_id', '<>', $data['exclude_type_id']);
            })
            ->where('meeting_id', $data['meeting_id'])
            ->get();

        $attendants = MeetingShowAttendantsMeetingResource::collection($attendants);

        return $attendants;
    }

    protected function searchAttendants($data)
    {
//        info($data);
        $attendants = Usuario::with(['config:id,etapa,logo',
            'invitations.meeting' => function ($q) use ($data) {
                $q->betweenScheduleDates($data);
                $q->ofReservedStatus();
                $q->excludeMeeting($data['meeting_id'] ?? null);
            }])
            ->when($data['usuarios_id'] ?? null, function ($q) use ($data) {
                $q->whereIn('id', $data['usuarios_id']);
            })
            ->when($data['usuarios_dni'] ?? null, function ($q) use ($data) {
                $q->whereIn('dni', $data['usuarios_dni']);
            })
            ->when($data['q'] ?? null, function ($q) use ($data) {
                $q->where(function ($q_where) use ($data) {
                    $q_where->where('email', 'like', "%{$data['q']}%");
                    $q_where->orWhere('nombre', 'like', "%{$data['q']}%");
                    $q_where->orWhere('dni', 'like', "%{$data['q']}%");
                });
            })
            ->when($data['grupos_id'] ?? null, function ($q) use ($data) {
                $q->whereHas('matricula_presente', function ($q2) use ($data) {
                    $q2->whereHas('matricula_criterio', function ($q3) use ($data) {
                        $q3->whereIn('matricula_criterio.criterio_id', $data['grupos_id']);
                    });
                });
            })
            ->when($data['config_id'] ?? null, function ($q) use ($data) {
                $q->where('config_id', $data['config_id']);
            })
            ->when($data['exclude_host_id'] ?? null, function ($q) use ($data) {
                $q->where('id', '<>', $data['exclude_host_id']);
            })
            ->select('id', 'dni', 'nombre', 'config_id')
            ->orderBy('config_id')
            ->orderBy('nombre')
            ->where('estado', ACTIVE)
            ->get();

        return $attendants;
    }

    protected function filterEmptyMeetingInvitations(&$attendants)
    {
        foreach ($attendants as $attendant) {
            foreach ($attendant->invitations as $key => $invitation) {
                if (!$invitation->meeting)
                    $attendant->invitations->pull($key);
            }
        }

        return true;
    }

    protected function isAvailableToHostMeeting($usuario, $dates, $meeting = NULL)
    {
        if (self::meetingsAsHost($usuario, $dates, $meeting)) return false;

//        return self::meetingsAsParticipant($user->usuario, $dates, $meeting) ? false : true;
        return self::meetingsAsParticipant($usuario, $dates, $meeting) ? false : true;
    }

    protected function meetingsAsHost($usuario, $dates, $meeting = NULL, $method = 'count')
    {
        $query = Meeting::where('host_id', $usuario->id);

        $query->excludeMeeting($meeting);

        $query->betweenScheduleDates($dates);
        $query->ofReservedStatus();

        return $query->$method();
    }

    protected function meetingsAsParticipant($usuario, $dates, $meeting = NULL, $method = 'count')
    {
        $query = Meeting::query();

        $query->excludeMeeting($meeting);

        $query->whereHas('attendants', function ($q) use ($usuario) {
            $q->where('usuario_id', $usuario->id);
        });

        $query->betweenScheduleDates($dates);
        $query->ofReservedStatus();

        return $query->$method();
    }

    protected function getUnavailableAttendants($data, $method = 'get')
    {
        $query = self::whereHas('meeting', function ($q) use ($data) {
            $q->betweenScheduleDates($data);
            $q->ofReservedStatus();
        });

        // TODO: Agregar filtro por usuario
        // $query->where('attendant.usuario_id', $request->usuario_id)
        // dd($query->toSql(), $request);

        return $query->$method();
    }

    protected function getUsuariosDniFromExcel($data)
    {
        $import = new AttendantsFormImport($data);
        Excel::import($import, $data['file']);
        $usuarios_dni = $import->get_data();

        return $usuarios_dni;
    }

    protected function mergeHostToAttendants($meeting, $host)
    {
        $user_type = Taxonomy::getFirstData('meeting', 'user', 'host');

        $current_host = $meeting->attendants()->where('type_id', $user_type->id)->first();

        return [
            'id' => $current_host->id ?? NULL,
            'type_id' => $user_type->id,
            'usuario_id' => $host->id
        ];
    }

    protected function addOrUpdateHostToMeeting($meeting, $host)
    {
        if ($meeting->host_id != $host->id) {
            $meeting->removeCurrentHost();

            return $this->addUsuarioToMeeting($meeting, $host, 'host');
        }

        return false;
    }

    protected function addUsuarioToMeeting($meeting, $usuario, $code)
    {
        $user_type = Taxonomy::getFirstData('meeting', 'user', $code);

        $meeting->attendants()->create(['type_id' => $user_type->id, 'usuario_id' => $usuario->id]);

        return true;
    }

    protected function createOrUpdatePersonalLinkMeeting($meeting, $datesHaveChanged)
    {

        switch ($meeting->account->service->code) {
            case 'zoom':
                $q_attendants = $meeting->attendants()->with('usuario');
//                if (!($meeting->status->code === 'scheduled' and $datesHaveChanged)){
                $q_attendants->whereNull('link');
//                }
                $attendants = $q_attendants->get();

//                info("USUARIOS A ACTUALIZAR PERSONAL LINK");
//                info($attendants->pluck('id'));
//                info($attendants->pluck('usuario.dni'));

                $result = ZoomService::prepareBatchAttendantsData($meeting, $attendants);
//                info("", $result);
                foreach ($result as $attendant_id => $attendant_data) {
                    Attendant::where('id', $attendant_id)->update($attendant_data);
                }
                break;

            case 'jitsi':
                // TODO $result = JitsiService::createOrUpdateMeeting($this, $data, $meeting);
                break;

            case 'vimeo':
                // TODO $result = VimeoService::createOrUpdateMeeting($this, $data, $meeting);
                break;

            default:
                break;
        }
    }

    protected function getMeetingAttendantsForMeeting($meeting)
    {
        $user_type = Taxonomy::getFirstData('meeting', 'user', 'host');

        $data = [
            'starts_at' => $meeting->starts_at,
            'finishes_at' => $meeting->finishes_at,
            'meeting_id' => $meeting->id,
            'exclude_type_id' => $user_type->id,
        ];

        return Attendant::getMeetingAttendants($data);
    }
}
