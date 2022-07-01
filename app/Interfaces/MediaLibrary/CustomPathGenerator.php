<?php

namespace App\Interfaces\MediaLibrary;

use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use Illuminate\Support\Str;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media) : string
    {
        $media->load('model', 'model_name');

        $model_name = $media->model_name->code ?? 'default';

        // if ($media->model instanceof Taxonomy)
        if ($model_name == 'taxonomy')
            return "{$media->model->group}-{$media->model->type}/";

        $name = Str::plural($model_name);
        
        return "{$name}/{$media->id}/";
    }

    public function getPathForConversions(Media $media) : string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}