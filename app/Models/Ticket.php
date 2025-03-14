<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Ticket extends BaseModel
{

    protected $fillable = [
        'user_id',
        'workspace_id',
        'reason',
        'detail',
        'dni',
        'email',
        'name',
        'contact',
        'info_support',
        'msg_to_user',
        'status',
    ];

    /*

    Mutators and accesors

--------------------------------------------------------------------------*/


    public function setInfoSupportAttribute($info): void
    {
        $this->attributes['info_support'] = json_encode($info);
    }

    public function getInfoSupportAttribute($info)
    {

        return $info ? json_decode($info) : null;
    }

    /*

        Relationships

    --------------------------------------------------------------------------*/


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    /*

        Methods

    --------------------------------------------------------------------------*/


    protected function search($request)
    {
        // Mark login tickets as 'solucionado'

        $this->markAsSolucionado();

        // $workspace = get_current_workspace();
        $subworkspaces = get_current_workspace_indexes();
        $query = self::with(['user','user.subworkspace:id,logo']);
        $query->whereHas('user', function ($q) use ($request, $subworkspaces){
            $q->whereIn('subworkspace_id', $subworkspaces['ids']);
        });
        // $query->where('workspace_id', $workspace->id);

        if ($request->q || $request->modulo) {

            $subworkspaceId = $request->modulo;
            // $subworkspaceId = Workspace::getWorkspaceIdFromModule($request->modulo);

            $query->where(function ($qu) use ($request, $subworkspaceId) {

                $qu->whereHas('user', function ($q) use ($request, $subworkspaceId) {

                    if ($request->q) {
                        $q->where('name', 'like', "%$request->q%");
                        $q->orWhere('dni', 'like', "%$request->q%");
                    }

                    if ($request->modulo)
                        $q->where('subworkspace_id', $subworkspaceId);
                });

                if ($request->q)
                    $qu->orWhere('id', 'like', "%$request->q%");
            });
        }

        if ($request->status)
            $query->where('status', $request->status);

        if ($request->reason)
            $query->where('reason', $request->reason);

        if ($request->starts_at)
            $query->whereDate('created_at', '>=', $request->starts_at);

        if ($request->ends_at)
            $query->whereDate('created_at', '<=', $request->ends_at);

        if (!is_null($request->sortBy)) {

            $request->sortBy = $request->sortBy == 'status' ? 'estado' : $request->sortBy;

            $field = $request->sortBy ?? 'created_at';
            $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

            $query->orderBy($field, $sort);
        } else {
            $query->orderBy('created_at', 'DESC');
        }

        return $query->paginate($request->paginate);
    }

    /**
     * Mark tickets as "solucionado" when user has logged in
     * after creating the ticket
     * @return void
     */
    protected function markAsSolucionado()
    {
        $subworkspaces = get_current_workspace_indexes();
        $subworkspacesIds = implode(',', $subworkspaces['ids']->toArray());
        if (!$subworkspacesIds) return;

        $usersToUpdate = DB::select(DB::raw("
            select
                u.id
            from users u
                join tickets t on t.user_id = u.id
            where u.last_login > t.created_at
                and t.status != 'solucionado'
                and t.reason = 'Soporte Login'
                and u.subworkspace_id in ($subworkspacesIds)
        "));
        $usersToUpdateIds = collect($usersToUpdate)->pluck('id');

        $tickets = Ticket::query()
            ->where('status', '!=', 'solucionado')
            ->where('reason', 'Soporte Login')
            ->whereIn('user_id', $usersToUpdateIds)
            ->get();

        $tickets_users_inactive = Ticket::query()->whereHas('user', function ($q) use ($subworkspaces){
            $q->whereIn('subworkspace_id', $subworkspaces['ids'])->where('active',0);
        })->where('status', 'pendiente')->get();

        $tickets = $tickets->merge($tickets_users_inactive);
        foreach ($tickets as $ticket) {
            $ticket->status = 'solucionado';
            $ticket->save();

            $ticket->updateInfoSupport('Plataforma', '');

            self::notifyUserAboutSolvedTicket($ticket);
        }
    }

    /**
     * Create a notification for user when its ticket is solved
     * @param Ticket $ticket
     * @return void
     */
    public static function notifyUserAboutSolvedTicket(Ticket $ticket): void
    {

        UserNotification::createNotifications(
            get_current_workspace()->id,
            [$ticket->user_id],
            UserNotification::TICKET_SOLVED,[
                'ticketNumber' => $ticket->id
            ]
        );
    }

    /**
     * Update values in info_support column
     */
    public function updateInfoSupport($solvedBy = null, $contactedBy = null) {
        $infoSupport = $this->info_support ?? new \stdClass();

        if ($solvedBy) {
            $infoSupport->solvedBy = $solvedBy;
        }

        if ($contactedBy) {
            $infoSupport->contactedBy = $contactedBy;
        }

        $this->info_support = $infoSupport;
        $this->save();
    }
}
