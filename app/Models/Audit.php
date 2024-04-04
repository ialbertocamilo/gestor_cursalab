<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Altek\Accountant\Models\Ledger;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\MorphTo;

use Carbon\Carbon;

class Audit extends MongoLedger
{
    protected $connection = 'mongodb';

    /*   class Audit extends Ledger
    use Ledger;

    protected $connection = 'mongodb';
    protected $rememberFor = WEEK_MINUTES;
    protected $table = 'ledgers';

    protected $fillable = ['title', 'body', 'slug', 'category_id', 'position', 'section_id', 'user_id', 'active', 'code', 'app_id'];
    protected $fillable = [];
    protected $guarded = [];

    protected $hidden=[
      'pivot'
    ]; */

    // protected $with = ['children', 'parent'];
    protected $excluded_fields = ['created_at', 'updated_at', 'id', 'password'];

    // relationships

    public function user(): ?MorphTo
    {
        return $this->morphTo();
    }

    public function model()
    {
        return $this->belongsTo(Taxonomy::class, 'recordable_type', 'path')
            ->where('group', 'system')
            ->where('type', 'model');
    }

    public function action_name()
    {
        return $this->belongsTo(Taxonomy::class, 'event', 'code')
            ->where('group', 'system')
            ->where('type', 'action');
    }

    public function event_name()
    {
        return $this->belongsTo(Taxonomy::class, 'event', 'code')
            ->where('group', 'system')
            ->where('type', 'event');
    }

    // methods

    public function countModifiedFieldsFiltered(): int
    {
        return count($this->getModifiedFieldsFiltered());
    }

    public function getModifiedFieldsFiltered(): array
    {
        if ($this->isBasicEvent()) {
            // parse to array
            $modified = is_string($this->modified) ?
                        json_decode($this->modified) : $this->modified;

            return array_diff($modified, $this->excluded_fields);
        }

        return $this->pivot['properties'] ?? [];
    }

    public function getDataFiltered(): array
    {
        return Arr::except($this->getData(true), $this->excluded_fields);
    }

    public function isBasicEvent()
    {
        return in_array($this->event, ['created', 'updated', 'deleted', 'impersonated', 'downloaded']);
    }

    public function getDataProcessed(): array
    {
        $filtered = $this->getDataFiltered();

        return $this->prepareData($filtered);
    }

    public function getModelProcessed(): array
    {
        $total = $modified = [];

        if ($this->isBasicEvent()) {
            try {
                // obtener modelo
                $model = $this->extract(false);
                // info(['model_ext' => $model]);
                // traer relaciones
                $model->loadDefaultRelationships();

                $relationships = $model->defaultRelationships;
                // info('getModelProceessed relationships');
                // info(['relationships' => $relationships]);

                // asignar valores al id (ejm status_id recibe data de status)
                $data = $this->prepareData($model);

                // info(['data' => $data]);

                // remover relaciones (ejm status) => array except relations
                $total = Arr::except($data, $relationships ?? []);
                // info(['relationships' => $relationships, 'total' => $total]);

                // separar solo modificados => array only modified
                $modifiedFields = $this->getModifiedFieldsFiltered();

                $modified = Arr::only($total, $modifiedFields);

            } catch (\Exception $e) {
                info($e);
            }
        }

        return compact('total', 'modified');
    }

    public function prepareData($model): array
    {
        $data = [];

        // $attributes = trans('validation.attributes');
        $array = $model->toArray();

        $relationships = $model->defaultRelationships;
        // info('prepareData relationships');
        // info($relationships);

        foreach ($array as $key => $item) {
            // info($key);
            $data[$key] = [
                'key' => $key,
                'label' => $this->getLabelName($key),
                'value' => $this->getValueName(
                    $item,
                    $key,
                    $array,
                    $relationships
                ),
                // 'modified' => null,
            ];
        }

        return $data;
    }

    public function getLabelName(string $key, array $attributes = []): string
    {
        $attributes = $attributes ?: trans('validation.attributes');

        return _ucwords(equivalent_value($attributes, $key, $key));
    }

    public function getAvailableString($item)
    {
        $available = true;

        foreach ($item as $key => $value) {
            if(!is_string($value)) {
                $available = false;
                break;
            }
        }
        return $available;
    }

    public function getValueName(mixed $item, $key, $array, $relationships)
    {
        $relationships_ids = array_keys($relationships ?? []);

        if (in_array($key, $relationships_ids)) {
            $relation = $relationships[$key];
            $item = $array[$relation];
        }

        // if ( is_bool($item) )
        if (in_array($key, ['active', 'active_results', 'assessable', 'evaluation_verified', 'enable_2fa', 'required'])) {
            return $item ? 'Sí' : 'No';
        }

        // === para arrays strings ===
        if (is_array($item) && $this->getAvailableString($item)) {
            $output_li = "";
            foreach ($item as $key => $value) $output_li .= "<li> $key - $value </li>";
            return " <ul class='pl-0 mb-0' style='list-style: none;'>
                        $output_li
                     </ul>";
        }
        // === para arrays strings ===

        if (is_array($item)) {
            return $item['title'] ??
                ($item['alias'] ?? ($item['name'] ?? 'ND'));
        }

        if (is_date($item) && !is_numeric($item)) {
            $item = Carbon::parse($item)->setTimezone('America/Lima');

            return $item->format('d/m/Y g:i a');
        }

        return $item;
    }

    public function getModelName()
    {
        $name = $this->model->name ?? 'No definido';

        if (!$this->isBasicEvent() and $name) {
            $name =
                $name .
                ' / ' .
                $this->getLabelName($this->pivot['relation'] ?? 'Relación');
        }
        return $name;
    }

    // Registros Nombre
    public function getRecordableName()
    {
        return $this->recordable->title ??
            ($this->recordable->fullname ??
                ($this->recordable->name ??
                    ($this->recordable->titulo ??
                        ($this->recordable->pregunta ??
                            ($this->recordable->nombre ??
                                '#ID ' .
                                    ($this->recordable->id ?? 'No definido'))))));
    }

    public function getModifiedLabels($modified)
    {
        $data = [];

        foreach ($modified as $key => $field) {
            $data[] = strtolower($field['label']);
        }

        return $data;
    }

    protected function search($request)
    {
        $query = self::with('user', 'model', 'action_name', 'event_name');

        // Event filter

        if ($request->events) {
            $query->whereIn('event', $request->events);
        }

        // Dates filter

        if ($request->date_range) {
            if (isset($request->date_range[1])) {
                $startDate = new Carbon($request->date_range[0]);
                $endDate = new Carbon($request->date_range[1]);
                $query->whereBetween('created_at', [
                    $startDate,
                    $endDate->addDay(1),
                ]);
            } else {
                $startDate = new Carbon($request->date_range[0]);
                $endDate = new Carbon($request->date_range[0]);
                $query->whereBetween('created_at', [
                    $startDate,
                    $endDate->addDay(1),
                ]);
            }
        }

        // Search field filter

        if ($request->us_search) {
            // Get users ids which names matches search
            // get_current_workspace()->id
            $usersIds = User::where(
                'users.name',
                'like',
                "%$request->us_search%"
            )->pluck('id');
            $query->whereIn('user_id', $usersIds->toArray());
        }

        // Models filter

        if ($request->model_type) {
            $query->where('recordable_type', $request->model_type);
        }

        if ($request->model_id) {
            $model_id = intval($request->model_id);
            $query->where('recordable_id', $model_id);
        }

        // Sort by date
        $field = $request->sortBy ?? 'created_at';

        $sort = $request->sortBy ? 'asc' : 'desc';
        $sort2 = $request->sortDesc ? 'desc' : 'asc';

        if ($request->sortBy && $request->sortDesc) {
            $query->orderBy($field, $sort2);
        } else {
            $query->orderBy($field, $sort);
        }

        // Paginate
        return $query->paginate($request->paginate);
    }
}
