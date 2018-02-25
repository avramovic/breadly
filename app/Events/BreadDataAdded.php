<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BreadDataAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $table;
    public $newRecordId;
    public $data;
    public $user;

    /**
     * Create a new event instance.
     *
     * @param $table
     * @param $newRecordId
     * @param $data
     * @param $user
     */
    public function __construct($table, $newRecordId, $data, $user)
    {
        $this->table       = $table;
        $this->newRecordId = $newRecordId;
        $this->data        = $data;
        $this->user        = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
//        return new PrivateChannel('channel-name');
    }
}
