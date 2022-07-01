<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Altek\Accountant\Contracts\Recordable;

use Spatie\MediaLibrary\Support\File;

use App\Traits\CustomAudit;
use App\Traits\CustomCRUD;

use DB;

class MediaX extends BaseMedia implements Recordable
{
    use CustomCRUD, CustomAudit;

    use SoftDeletes;

    public $defaultRelationships = ['model_type' => 'model_name'];

    public function model_name()
    {
        return $this->belongsTo(Taxonomy::class, 'model_type', 'path')
            ->where('group', 'system')->where('type', 'model');
    }

    public function mime_type_name()
    {
        return $this->belongsTo(Taxonomy::class, 'mime_type', 'code')
            ->where('group', 'mime');
    }

    //  public function loadDefaultRelationships()
    // {
    //     $relationships = array_values($this->defaultRelationships);

    //     return $this->load($relationships);
    // }

    protected function loadData($data = [], $module = null)
    {
        $query = Media::with('model', 'model_name', 'mime_type_name.parent')->latest();

        if ($data->search_text)
            $query->where('name', 'like', "%$data->search_text%");

        if ($data->model):
            $modules = Taxonomy::whereIn('id', $data->model)->where('group', 'system')->where('type', 'model')->pluck('name');
            $query->whereIn('model_type', $modules);
        endif;

        if ($data->types):
            $types = Taxonomy::whereIn('parent_id', $data->types)->pluck('code');
            $query->whereIn('mime_type', $types);
        endif;

        if ($data->start_date)
            $query->whereDate('created_at', '>=', $data->start_date);

        if ($data->end_date)
            $query->whereDate('created_at', '<=', $data->end_date);

        return $query->paginate(18);
    }

    protected function getTotalStorageUsed()
    {
        $total = Media::sum('size');

        return File::getHumanReadableSize($total);
    }


}
