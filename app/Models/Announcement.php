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

    // public function subworkspaces(): BelongsToMany
    // {

    //     return $this->belongsToMany(
    //         Workspace::class,
    //         'criterion_value_announcements',
    //         'announcement_id',
    //         'criterion_value_id'
    //     );
    // }

    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
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
        $query = self::withCount(['segments']);

        if ($data->q)
            $query->where('nombre', 'like', "%$data->q%");

        $modules = $data->module ? [$data->module]
            : Workspace::where('parent_id', $data['workspace_id'])->pluck('criterion_value_id');

        $query->whereHas('criterionValues', function ($q) use ($modules) {
            return $q->whereIn('criterion_value_id', $modules);
        });

        if ($data->active == 1)
            $query->where('active', ACTIVE);

        if ($data->active == 2)
            $query->where('active', '<>', ACTIVE);


        // Set sorting values
        if (!is_null($data->sortBy)) {

            $field = $data->sortBy ?? 'publish_date';
            $sort = $data->sortDesc == 'true' ? 'DESC' : 'ASC';

            $query->orderBy($field, $sort);
        } else {
            $query->orderBy('created_at', 'DESC');
        }

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
            ->select(DB::raw("nombre, contenido, imagen, destino, link, archivo, DATE_FORMAT(publish_date,'%d/%m/%Y') AS publish_date_formatted, publish_date AS inicio, end_date AS fin"))
            // ->where('config_id', 'like', "%$subworkspace_id%")
            ->where('active', ACTIVE)
            ->where(function ($query) {

                $query->where(function ($q) {
                    $q->whereNull('publish_date');
                    $q->orWhereDate('publish_date', '<=', date('Y-m-d'));
                });
                $query->where(function ($q) {
                    $q->whereNull('end_date');
                    $q->orWhereDate('end_date', '>=', date('Y-m-d'));
                });
            })
            ->orderBy('publish_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    // Save list of modules

    public static function saveModules($announcementId, $modulesId)
    {
    }

    /**
     * Register notifications for all active users in announcement's modules
     * @param $announcementId
     * @return void
     */
    public static function registerNotificationsForAnnouncement($announcementId): void
    {

        // Load announcement's subworkspaces ids

        $subworkspacesIds = DB::select(DB::raw('
                select w.id
                from criterion_value_announcements cva
                    join workspaces w on w.criterion_value_id = cva.criterion_value_id
                where cva.announcement_id = :announcement_id
            '),['announcement_id' => $announcementId]
        );

        $subworkspacesIds = collect($subworkspacesIds)
            ->pluck('id')
            ->toArray();

        // Load users ids from subworkspaces

        $usersIds = User::query()
            ->whereIn('subworkspace_id', $subworkspacesIds)
            ->where('active', 1)
            ->select('id')
            ->pluck('id')
            ->toArray();

        // Register notifications

        UserNotification::createNotifications(
            get_current_workspace()->id,
            $usersIds,
            UserNotification::NEW_ANNOUNCEMENT,
            [ ],
            null
        );
    }
}
