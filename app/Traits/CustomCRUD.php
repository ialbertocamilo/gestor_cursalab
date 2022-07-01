<?php

namespace App\Traits;

// use Illuminate\Support\Facades\Cache;
use App\Traits\ApiResponse;
use App\Models\BaseModel;
use App\Events\PublicEvent;
use DB;

trait CustomCRUD
{
    use ApiResponse;

    protected function storeRequest($data, $model = null, $files = [])
    {
        try {

            DB::beginTransaction();

            extract( self::storeOrUpdate($data, $model) );

            foreach ($files as $key => $name)
            {
                $model->setMediaCollection($data, $name);
            }

            DB::commit();

            // if ($model instanceof \App\Models\Channel)
            // {
            //     if (in_array('account_id', array_keys($model->getChanges())))
            //     {
            //         \Artisan::call('modelCache:clear', array('--model' => "App\Models\Account"));
            //     }
            // }


        }catch (\Exception $e){

            info($e);

            DB::rollBack();

            return $this->error($e->getMessage());
        }

        if ($files)
        {
            foreach ($files as $key => $name)
            {
                $model->getFirstMediaToForm($name);
            }
        }

        // info('storeRequest CRUD');

        PublicEvent::dispatch($message);

        return $this->success($model, $message);
    }

    protected function storeOrUpdate($data, $model = null)
    {
        if ( $model ):

            $model->update($data);

            $message = 'Registro actualizado correctamente';

        else:

            $model = BaseModel::create($data);

            $message = 'Registro creado correctamente';

        endif;

        return compact('model', 'message');
    }

    protected function destroyRequest($model = null)
    {
        try {

            $model = $model ?? $this;

            // $instance = $model instanceof \App\Models\Channel;

            DB::beginTransaction();

            // $model->clearMediaCollection();

            // $model->forceDelete();

            $model->delete();

            $message = 'Registro eliminado correctamente';

            DB::commit();

            if ($model instanceof \App\Models\Channel)
            {
                \Artisan::call('modelCache:clear', array('--model' => "App\Models\Account"));
            }
        }
        catch (\Exception $e){

            DB::rollBack();

            return $this->error($e->getMessage());
        }

        return $this->success($model, $message);
    }

    public function changeStatus($model = null)
    {
        DB::beginTransaction();

        try {

            $model = $model ?? $this;

            $model->active = ! $model->active;

            $model->update();

            DB::commit();

        }catch (\Exception $e){

            DB::rollBack();

            return $this->error($e->getMessage());
        }

        // return $this->success($model, 'Estado actualizado correctamente.');
        return $this->success([], 'Estado actualizado correctamente.');
    }

    protected function getDataForSelect($fields = [])
    {
        $fields = $fields ?: ['id', 'name'];

        return $this->where('active', ACTIVE)->get($fields);
    }

    protected function getDataWithMediaForSelect($collection, $fields = [], $only = [], $default = 'default_photo.png')
    {
        $fields = $fields ?: ['id', 'name', 'description'];
        $rows = $this->with('media')
                    ->when($only, function($q) use ($only){
                        $q->whereIn('id', $only);
                    })
                    // ->where('active', ACTIVE)
                    ->get($fields);

        foreach ($rows as $key => $row)
        {
            $row->getFirstMediaToForm($collection);

            $row->image = $row->$collection['media_placeholder'] ?: $default;
        }

        return $rows->makeHidden([$collection, 'media']);
    }
}

