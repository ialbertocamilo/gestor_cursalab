<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MassiveUploadProgressEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    private $name_chanell;
    private $percent;
    public function __construct($name_chanell,$percent)
    {
        $this->name_chanell = $name_chanell;
        $this->percent = $percent;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->name_chanell);
        // return new Channel('upload-topic-grades.'.Auth::user()->id.'.'.$this->number_socket);
        // return new PrivateChannel('upload-topic-grades.'.auth()->id.'-'.$percent);
    }

    public function broadcastWith(){
        return [
            'percent' => $this->percent,
        ];
    }
}
