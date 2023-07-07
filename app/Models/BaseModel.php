<?php

namespace App\Models;

use Altek\Accountant\Contracts\Recordable;
use App\Traits\CustomAudit;
use App\Traits\CustomCRUD;
use App\Traits\UnixTimestampSerializable;
use App\Utils\HasManySyncable;
use Cviebrock\EloquentSluggable\Sluggable;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

// use Watson\Rememberable\Rememberable;

// use Keygen\Keygen;

// class BaseModel extends Model implements HasMedia, Recordable
class BaseModel extends Model implements Recordable
{
    // use InteractsWithMedia;
    // use Rememberable;
    use SoftDeletes;
    use HasFactory;
    use Sluggable;

    // use UnixTimestampSerializable;

    // use CustomMedia, CustomCRUD, CustomAudit;
    use CustomCRUD, CustomAudit;

    // use Cachable;
    use Cachable {
        Cachable::getObservableEvents insteadof CustomAudit;
        Cachable::newBelongsToMany insteadof CustomAudit;
        Cachable::newMorphToMany insteadof CustomAudit;
    }

    protected $casts = [
        'active' => 'boolean',
    ];


    protected $defaultRelationships = [];

    public function sluggable(): array
    {
        return [];
    }

    protected function logQuery()
    {
        DB::listen(function($query){
            info($query->sql);
        });
    }

    // Message Functions

    protected function successResponse($params = [])
    {
        // Cache::flush();

        $data = [
            'status' => 'success',
            'data' => $params['data'] ?? null,
            'message' => $params['message'] ?? '¡Éxito!',
        ];

        return $data;
    }

    protected function errorResponse($params = [])
    {
        $message = $params['message'] ?? '¡Ocurrió un error!';

        if ( app()->environment('local') ) :
            info($params['exception']);
            $exception = $params['exception']->getMessage() ?? null;
            $message = $exception ? $message . ' :: ' . $exception : $message;

        endif;

        return abort(500, $message);
    }

    // public function setCodeAttribute($value = '')
    // {
        // if ( ! empty ($value) )
        // {
            // $this->attributes['code'] = $this->generateCode($value);
        // }
    // }

    public function hasManySync($related, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return new HasManySyncable(
            $instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey
        );
    }

    // public function audits(): MorphMany
    // {
    //     return $this->morphMany(Config::get('accountant.ledger.implementation'), 'recordable');
    // }

    public function getFullnameAttribute()
    {
        if ($this->lastname)
            return $this->name . ' ' . $this->lastname;

        return $this->name;
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === 'true' OR $value === true OR $value === 1 OR $value === '1');
    }

    public function generateCode(string $keys = '')
    {
        // $code = $this->getClassCode();

        // $prefix = 'CM-' . $code . '-' . ($keys ? $keys . '-' : $keys);

        // $key = Keygen::numeric(21)->prefix($prefix)->generate(function($key) {
        //     return join('-', str_split($key, 4));
        // });

        // return $key;
    }

    public function getClassCode()
    {
        $model_name = class_basename($this);

        $code = preg_replace('~[^A-Z]~', '', $model_name);

        return $code;
    }

    // public function registerMediaConversions(Media $media = null): void
    // {
    //     $this
    //         ->addMediaConversion('thumb')
    //         // ->width(350)
    //         // ->height(350)
    //         ->crop('crop-center', 350, 350)
    //         ->keepOriginalImageFormat();

    //     $this
    //         ->addMediaConversion('main')
    //         ->fit('contain', 1920, 1080)
    //         ->quality(85)
    //         ->withResponsiveImages()
    //         // ->performOnCollections('logo', 'etc')
    //         ->keepOriginalImageFormat();
    // }

    // public static function bootInteractsWithMedia()
    // {
    //     static::deleting(function (HasMedia $model) {

    //         if ($model->shouldDeletePreservingMedia())
    //             return;

    //         $model->media()->cursor()->each(fn (Media $media) => $media->delete());
    //     });
    // }

    public static function definedRelations(): array
    {
          $reflector = new \ReflectionClass(get_called_class());

          return collect($reflector->getMethods())
              ->filter(
                  fn($method) => !empty($method->getReturnType()) &&
                      str_contains(
                          $method->getReturnType(),
                          'Illuminate\Database\Eloquent\Relations'
                     )
              )
              ->pluck('name')
              ->all();
     }

     public static function generateExternalApiPageData($data,$resource='data')
     {
        return [
            $resource=>$data->items(),
            'current_page' => $data->currentPage(),
            'last_page'=>$data->lastPage(),
            'per_page'=>$data->count(),
            'prev_page_url'=>$data->previousPageUrl(),
            'nex_page_url'=>$data->nextPageUrl(),
            'total'=>$data->total(),
        ];
    }

    public function replicateWithRelationsAttributes(): static
    {
        $model = clone $this->replicate();
        foreach ($this->getRelations() as $key => $relation) {
           $model->setAttribute($key, clone $relation);
        }

        return $model;

    }
}
