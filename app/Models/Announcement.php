<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class Announcement extends BaseModel
{
    protected $table = 'announcements';

    protected $fillable = [
        'module_id',
        'nombre',
        'contenido',
        'imagen',
        'archivo',
        'destino',
        'link',
        'position',
        'active',
        'publish_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $dates = [
       'publish_date'
    ];

    /*

        Methods

    --------------------------------------------------------------------------*/


    /**
     * Delete record
     *
     * @return bool|null
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * Load and filter records
     *
     * @param $request
     * @return LengthAwarePaginator
     */
    protected function search($request)
    {
        $query = self::query();

        if ($request->q)
            $query->where('nombre', 'like', "%$request->q%");

        if ($request->module) {
            $query->where(
                'module_id', 'like', "%$request->module%"
            );
        }

        // Set sorting values

        $field = $request->sortBy ?? 'publish_date';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    /**
     * Get formatted publication date
     *
     * @return string
     */
    public function getPublicationDate()
    {

          if ( is_null($this->publish_date) ) {
              return 'Indefinido';
          }

        return $this->publish_date->format('d/m/Y');
    }

    protected function getPublisheds($module_id = NULL)
    {

        return DB::table('announcements')
            ->select(DB::raw("nombre, contenido, imagen, destino, link, archivo, DATE_FORMAT(publish_date,'%d/%m/%Y') AS publish_date"))
            ->where('module_id', 'like', '%"' . $module_id . '"%')
            ->where('active', ACTIVE)
            ->where(function($query){

                $query->where(function($q){
                    $q->whereNull('publish_date');
                    $q->orWhereDate('publish_date', '<=', date('Y-m-d'));
                });
            })
            ->orderBy('publish_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
