<?php

namespace App\Http\Resources;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VademecumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {

        if ($this->media != null) {
            $hasScormRoute =
                ($this->media_type === 'scorm'  || $this->media_type == null) &&
                $this->media->file != null;
            $file = generateSignedUrl('scorm/' . $this->media->title);

        } else {
            $hasScormRoute = false;
            $file = null;
        }

        return [
            'id' => $this->id,
            'name' => clean_html($this->name, 50),
            'modules' => $this->getModules(),
            'images' => $this->getModulesImages(),
            'active' => $this->active ? true : false,

            'category_id' => $this->category->name ?? 'No definido',
            'subcategory_id' => $this->subcategory->name ?? 'No definido',
            'is_super_user' => auth()->user()->isAn('super-user'),

            'scorm_route' => $hasScormRoute ? $file : null,
            'has_scorm_route' => $hasScormRoute,

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
        ];
    }

    public function getModulesImages()
    {
        $data = [];

        foreach($this->modules AS $module)
        {
            $subworkspace = Workspace::findSubworkspaceWithCriterionValueId(
                $module->id
            );

            $data[] = [
                'name' => $module->value_text,
                'image' => $subworkspace ? space_url($subworkspace->logo) : ''
            ];
        }

        return $data;
    }

    /**
     * Generate a string with modules names separated by commas
     *
     * @return string
     */
    public function getModules()
    {
        $modules = $this->modules->pluck('value_text')->toArray();
        return implode(', ', $modules);
    }
}
