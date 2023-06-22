<?php

namespace App\Models;

use App\Services\FileService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speaker extends BaseModel
{
    protected $table = 'speakers';

    protected $fillable = [
        'workspace_id',
        'name',
        'biography',
        'email',
        'specialty',
        'image',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function benefits()
    {
        return $this->hasMany(Benefit::class, 'speaker_id', 'id');
    }

    public function experiences()
    {
        return $this->hasMany(SpeakerExperience::class, 'speaker_id', 'id');
    }

    protected function getInfo( $data )
    {
        $response['data'] = null;
        $speaker_id = $data['speaker'];

        // $workspace = get_current_workspace();

        $speaker = Speaker::with(
            ['experiences','benefits'
        ])
        ->where('active',1)
        ->where('id', $speaker_id->id)
        ->first();
        // where('workspace_id', $workspace->id)

        return ['data'=>$speaker];
    }

    protected function getSpeakers()
    {
        $workspace = get_current_workspace();
        $speakers_items = Speaker::where('active',1)
                        // ->where('workspace_id', $workspace->id)
                        ->orderBy('name', 'DESC')
                        ->get();
        foreach($speakers_items as $speaker) {
            $speaker->image = $speaker->image ? FileService::generateUrl($speaker->image) : $speaker->image;
        }

        $response['data'] = $speakers_items;

        return $response;
    }
}
