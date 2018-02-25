<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BreadFileUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $table;
    public $column;
    public $id;
    public $base64Data;
    public $user;

    /**
     * Create a new event instance.
     *
     * @param $table
     * @param $column
     * @param $id
     * @param $base64Data
     * @param $user
     */
    public function __construct($table, $column, $id, $base64Data, $user)
    {
        //
        $this->table      = $table;
        $this->column     = $column;
        $this->id         = $id;
        $this->base64Data = $base64Data;
        $this->user       = $user;
    }

    public function getFileData()
    {
        return base64_decode($this->base64Data);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
