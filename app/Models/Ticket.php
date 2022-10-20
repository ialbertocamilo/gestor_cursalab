<?php

namespace App\Models;

class Ticket extends BaseModel
{

    protected $fillable = [
        'user_id',
        'workspace_id',
        'reason',
        'detail',
        'dni',
        'name',
        'contact',
        'info_support',
        'msg_to_user',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

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
        $query = self::with(['workspace', 'user']);

        if ($request->q || $request->modulo) {

            $subworkspaceId = Workspace::getWorkspaceIdFromModule($request->modulo);

            $query->where(function ($qu) use ($request, $subworkspaceId) {

                $qu->whereHas('user', function ($q) use ($request, $subworkspaceId) {

                    if ($request->q) {
                        $q->where('name', 'like', "%$request->q%");

                        //                        if (strlen($request->q) > 4)
                        //                            $q->orWhere('dni', 'like', "%$request->q%");
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
}
