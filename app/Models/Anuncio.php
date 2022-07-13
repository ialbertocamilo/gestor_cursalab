<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use DB;

// ALTER TABLE `anuncios` 
// ADD COLUMN `publication_starts_at` datetime(0) NULL AFTER `active`,
// ADD COLUMN `publication_ends_at` datetime(0) NULL AFTER `publication_starts_at`,
// MODIFY COLUMN `publish_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `active`;

// ALTER TABLE `anuncios` 
// MODIFY COLUMN `publish_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP AFTER `active`;

class Anuncio extends Model 
{
    protected $table = 'announcements';

    protected $fillable = [
    	'nombre', 'module_id', 'resumen', 'contenido', 'cod_video', 'imagen', 'archivo', 'video', 'destino', 'link', 'orden', 'active', 'created_at', 'updated_at', 'publication_starts_at', 'publication_ends_at'
    ];

    protected $dates = ['publication_starts_at', 'publication_ends_at', 'publish_date'];

    protected $casts = [
        'publication_starts_at' => 'date:Y-m-d',
        'publication_ends_at' => 'date:Y-m-d',
    ];

    // public function modules()
    // {
    //     $config_ids = json_decode($this->config_id, true);

    //     return $this->hasMany(Abconfig::class)
    //     // code...
    // }

    // public function setEstadoAttribute($value)
    // {
    //     $this->attributes['active'] = ($value==='true' OR $value === true OR $value === 1 OR $value === '1' );
    // }

    public function delete()
    {
        //$this->temas()->delete();
        
        return parent::delete();
    }

    protected function search($request)
    {
        $query = self::query();

        if ($request->q)
            $query->where('nombre', 'like', "%$request->q%");

        if ($request->module)
            $query->where('module_id', 'like', "%$request->module%");

        // $query->orderBy('orden','DESC');

        $field = $request->sortBy ?? 'publication_ends_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
        
        // $sort = $request->sortDesc ? ($request->sortDesc == 'true' ? 'DESC' : 'ASC') : 'DESC';
        
        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    public function getPublicationDate()
    {
        if ( is_null($this->publication_starts_at) AND is_null($this->publication_ends_at) )
            return 'Indefinido';

        if ( $this->publication_starts_at == $this->publication_ends_at )
            return $this->publication_starts_at->format('d/m/Y');

        $start = $this->publication_starts_at ? $this->publication_starts_at->format('d/m/Y') : 'No definido';
        $end = $this->publication_ends_at ? $this->publication_ends_at->format('d/m/Y') : 'No definido';

        return "{$start} - {$end}";
    }

    protected function getPublisheds($module_id = NULL)
    {
        // $anuncios = DB::table('anuncios')->select(DB::raw("nombre, contenido, imagen, destino, link, archivo, DATE_FORMAT(publish_date,'%d/%m/%Y') AS publish_date"))->where('config_id', 'like', '%"' . $config_id . '"%')->where('active', 1)->orderBy('orden', 'DESC')->get();
        $anuncios = DB::table('announcements')
            ->select(DB::raw("nombre, contenido, imagen, destino, link, archivo, DATE_FORMAT(publication_starts_at,'%d/%m/%Y') AS publish_date, publication_starts_at AS inicio, publication_ends_at AS fin"))
            ->where('module_id', 'like', '%"' . $module_id . '"%')
            // ->where('module_id', $module_id)
            ->where('active', ACTIVE)
            ->where(function($query){
                $query->where(function($q){
                    $q->whereNull('publication_starts_at');
                    $q->orWhereDate('publication_starts_at', '<=', date('Y-m-d'));
                });
                $query->where(function($q){
                    $q->whereNull('publication_ends_at');
                    $q->orWhereDate('publication_ends_at', '>=', date('Y-m-d'));
                });
            })
            ->orderBy('publication_starts_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            // ->orderBy('orden', 'DESC')
            ->get();

        return $anuncios;
    }
}
