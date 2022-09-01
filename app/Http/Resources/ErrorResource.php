<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $emoji = $this->getPlatformEmoji();

        return [
            'id' => $this->id,
            'message' => $emoji . ' ' . clean_html($this->message, 45),
            'file' => clean_html($this->file, 30) . " [{$this->line}]",

            'custom_error' => [
                'title' => $emoji . ' ' . $this->url,
                'subtitle' => clean_html($this->message, 80),
            ],
            'status' => [
                'color' => config('errors.status-colors')[$this->status->code ?? ''],
                'text' => $this->status->name ?? 'No definido',
            ],
            'platform' => $this->platform->name ?? 'No definido',
            'user' => $this->user->name ?? 'Anónimo',
            // 'image' => space_url($this->imagen),
            // 'active' => $this->estado ? true : false,

            'orden' => $this->orden,
            // 'body' => clean_html($this->content, 30),

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
        ];
    }

    public function getPlatformEmoji()
    {
        $emoji = '❔';

        if ( $this->platform->code == 'app' ) $emoji = '📱';
        
        if ( $this->platform->code == 'gestor' ) $emoji = '💻';
        
        return $emoji;
    }
}
