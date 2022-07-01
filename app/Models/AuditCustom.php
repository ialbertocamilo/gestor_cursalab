<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditCustom extends Model
{
    protected $table = 'audits';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user_rest()
    {
        return $this->belongsTo(Usuario_rest::class, 'user_id');
    }

    public function section()
    {
        return $this->belongsTo(Taxonomia::class, 'auditable_type', 'code');
    }

    protected function search($request, $paginate = 15)
    {
        $q = $this->prepareSearchQuery($request);

        return $q->paginate($paginate);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public function getRecordableName()
    {
        return $this->auditable->etapa ?? $this->auditable->nombre ?? $this->auditable->titulo ?? '#ID ' . ($this->auditable->id ?? 'Undefined');
    }


    protected function prepareSearchQuery($request)
    {
        $q = self::with('user', 'user_rest', 'section', 'auditable')
            ->where(function ($q) {
                $q->where('tags', 'like', '%modelo_independiente%')
                    ->orWhere('tags', null);
            });
            // ->orderBy('created_at', 'DESC');

        if ($request->events)
            $q->whereIn('event', $request->events);

        if ($request->resources)
            $q->whereIn('auditable_type', $request->resources);

        if ($request->search_in) {
            $search_in = $request->search_in;
            $q->where('user_type', '=', "App\\$search_in");
            $relation = $search_in === 'User' ? 'user' : 'user_rest';
            $field_name = $search_in === 'User' ?
                "users.name like ? or users.email like ?"
                : "usuarios.nombre like ? or usuarios.dni like ?";
            if ($request->us_search) {
                $q->whereHas($relation, function ($query) use ($field_name, $request) {
                    $query->where(function ($query2) use ($field_name, $request) {
                        $query2->whereRaw($field_name, ["%$request->us_search%", "%$request->us_search%"]);
                    });
                }
                );
            }
        }

        if ($request->date_range) {
            $q->whereDate('created_at', '>=', $request->date_range[0]);
            $q->whereDate('created_at', '<=', $request->date_range[1]);
        }

        $field = $request->sortBy ?? 'created_at';

        $sort = 'DESC';

        if ($request->sortDesc)
        {
            $sort = $request->sortDesc == 'true' ? 'ASC' : 'DESC';
        }
        
        $q->orderBy($field, $sort);

        return $q;
    }
}
