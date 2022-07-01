<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Criterio extends Model
{
    protected $table = 'criterios';

    protected $fillable = [
        'id', 'valor', 'tipo_criterio', 'tipo_criterio_id', 'config_id'
    ];
     //relacion con tabla tipo criterios
     public function tipo_criterio()
     {
         return $this->belongsTo(TipoCriterio::class);
     }

     public function config()
     {
         return $this->belongsTo(Abconfig::class, 'config_id');
     }

     public function modulo()
     {
         return $this->belongsTo(Abconfig::class, 'config_id');
     }

    /* AUDIT TAGS */
    public function generateTags(): array
    {
        return [
            'modelo_independiente',
        ];
    }
    /* AUDIT TAGS */

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'grupo');
    }

    protected function search($request)
    {
        $query = self::with('config')->withCount('usuarios');

        if ($request->q)
            $query->where('valor', 'like', "%$request->q%");

        if ($request->modulo)
            $query->where('config_id', 'like', "%$request->modulo%");

        if ($request->tipo_criterio)
            $query->where('tipo_criterio_id', 'like', "%$request->tipo_criterio%");

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        // $query->orderBy('orden','DESC');

        return $query->paginate($request->paginate);
    }

    protected function getForSelect($tipo, $config_id = NULL)
    {
        $tipo = TipoCriterio::where('nombre', $tipo)->first();

        return Criterio::select('id', 'valor as nombre')
                        ->where('tipo_criterio_id', $tipo->id)
                        ->when($config_id, function($q) use ($config_id){
                            $q->where('config_id', $config_id);
                        })
                        ->get();
    }

    public function supervisores()
    {
        return $this->hasMany(Supervisor::class, 'criterio_id');
    }

    protected function getGruposForSelect($params = [])
    {
        $grupos = Criterio::with('config:id,codigo_matricula')
            ->when($params['config_id'] ?? null, function ($q) use($params){
                $q->where('config_id', $params['config_id']);
            })
            ->get(['id', 'valor', 'config_id']);

        foreach ($grupos as $grupo) {
            $grupo->nombre = "{$grupo->config->codigo_matricula} - {$grupo->valor}";
            unset($grupo['config'], $grupo['config_id'], $grupo['valor']);
        }

        return $grupos;
    }
}
