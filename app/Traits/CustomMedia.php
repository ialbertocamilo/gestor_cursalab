<?php

namespace App\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait CustomMedia
{

    public function getFirstMediaToForm($collection, $field = null, $conversion = 'thumb', $default = null)
    {
        // $this->load('media');

        $resource = $this->getFirstMedia($collection);
        // $resource = $resource AND $resource->deleted_at ? NULL : $resource;

        $field = $field ?? $collection;

        $this->$field = [
            'file' => null,
            'media_id' => $resource->id ?? null,
            'media_placeholder' => $resource ? $resource->getFullUrl($conversion) : $default,
            'properties' => $resource->custom_properties ?? [],
        ];
    }

    protected function setMediaCollection($data, $field, $model = null)
    {
        // info($data[$field]);

        $model = $model ?? $this;
        $title = $model->title ?? $model->name ?? 'Default';
        $collection = $field;

        $properties = $data[$field]['properties'] ?? [];


        
        // $field_key = $this->getMediaFieldKey($field);

        // si viene el id del recurso
        if ( ! empty($data[$field]['media_id']) )
        {
            $id = $data[$field]['media_id'];

            $resource = $model->getFirstMedia($collection);

            // si el id es el mismo que el actual, es el mismo archivo
            if ( $resource AND ($resource->id == $id) )
            {
                // // si el nombre del modelo es distinto al del recurso, se actualiza
                // if ($resource->name != $title)
                    $resource->update(['name' => $title, 'custom_properties' => $properties]);

                return true;
            }

            // si el id es distinto, se copia el archivo a la colección del modelo
            $resource_selected = Media::findOrFail($id);

            return $resource_selected->copy($model, $collection);
        }

        // si viene un archivo en el request, se agrega a la colección
        if ( ! empty($data[$field]['file']) )
        {
            return $model->addMedia($data[$field]['file'])->usingName($title)->withCustomProperties($properties)->toMediaCollection($collection);
        }

        // si viene un archivo en base 64, se agrega a la colección
        if ( ! empty($data[$field]['base64']) )
        {
            return $model->addMediaFromBase64($data[$field]['base64'])->usingName($title)->withCustomProperties($properties)->toMediaCollection($collection);
        }

        // si no viene id ni archivo en el request, se elimina el recurso actual
        $resource = $model->getFirstMedia($collection);

        if ($resource)
            return $resource->delete();

        return false;
    }

    // protected function getMediaFieldKey($field, $key = 'id')
    // {
    //     return "{$field}_{$key}";
    // }

}

