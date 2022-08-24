<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Announcement extends BaseModel
{
    protected $fillable = [
        'nombre',
        'contenido',
        'imagen',
        'archivo',
        'destino',
        'link',
        'position',
        'active',
        'publish_date',
        'end_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $dates = [
        'publish_date',
        'end_date'
    ];

    /*
        Relationships
    --------------------------------------------------------------------------*/

    public function criterionValues(): BelongsToMany
    {

        return $this->belongsToMany(
            CriterionValue::class,
            'criterion_value_announcements',
            'announcement_id',
            'criterion_value_id'
        );
    }

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
     * @param $data
     * @return LengthAwarePaginator
     */
    protected function search($data)
    {
        $query = self::query();

        if ($data->q)
            $query->where('nombre', 'like', "%$data->q%");

        if ($data->module) {
            $query->whereHas('criterionValues', function ($q) use ($data) {
                return $q->where('criterion_value_id', $data->module);
            });
        }

        // Set sorting values

        $field = $data->sortBy ?? 'publish_date';
        $sort = $data->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($data->paginate);
    }

    /**
     * Get formatted publication date
     *
     * @return string
     */
    public function getPublicationDate(): string
    {

        $publishDate = 'Indefinido';
        if (!is_null($this->publish_date)) {

            $publishDate = $this->publish_date->format('d/m/Y');
        }

        $endDate = 'Indefinido';
        if (!is_null($this->end_date)) {

            $endDate = $this->end_date->format('d/m/Y');
        }

        return ($publishDate === 'Indefinido' && $endDate === 'Indefinido')
                ? 'Indefinido'
                : "$publishDate - $endDate";
    }

    protected function getPublisheds($subworkspace_value_id = NULL)
    {
        return Announcement::whereRelation('criterionValues', 'id', $subworkspace_value_id)
            // select(DB::raw("nombre, contenido, imagen, destino, link, archivo, DATE_FORMAT(publish_date,'%d/%m/%Y') AS publish_date"))
            ->select(DB::raw("nombre, contenido, imagen, destino, link, archivo, DATE_FORMAT(publish_date,'%d/%m/%Y') AS publish_date, publish_date AS inicio, end_date AS fin"))
            // ->where('config_id', 'like', "%$subworkspace_id%")
            ->where('active', ACTIVE)
            ->where(function ($query) {

                $query->where(function($q){
                    $q->whereNull('inicio');
                    $q->orWhereDate('inicio', '<=', date('Y-m-d'));
                });
                $query->where(function($q){
                    $q->whereNull('fin');
                    $q->orWhereDate('fin', '>=', date('Y-m-d'));
                });
            })
            ->orderBy('inicio', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    // Save list of modules

    public static function saveModules($announcementId, $modulesId) {



    }
}
