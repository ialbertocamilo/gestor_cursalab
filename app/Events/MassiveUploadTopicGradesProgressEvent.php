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

class MassiveUploadTopicGradesProgressEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    private $percent;
    private $number_socket;
    public function __construct($percent,$number_socket)
    {
        $this->percent = $percent;
        $this->number_socket = $number_socket;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('upload-topic-grades.'.Auth::user()->id.'.'.$this->number_socket);
        // return new PrivateChannel('upload-topic-grades.'.auth()->id.'-'.$percent);
    }

    public function broadcastWith(){
        return [
            'percent' => $this->percent,
        ];
    }
}
