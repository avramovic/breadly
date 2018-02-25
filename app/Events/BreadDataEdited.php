<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BreadDataEdited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $table;
    public $rowId;
    public $row;
    public $data;
    public $user;

    /**
     * Create a new event instance.
     *
     * @param $table
     * @param $rowId
     * @param $row
     * @param $data
     * @param $user
     */
    public function __construct($table, $rowId, $row, $data, $user)
    {
        //
        $this->table = $table;
        $this->rowId = $rowId;
        $this->row   = $row;
        $this->data  = $data;
        $this->user = $user;
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
