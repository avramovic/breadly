<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BreadDataDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $table;
    public $entity;
    public $softDeleted;
    public $user;

    /**
     * Create a new event instance.
     *
     * @param $table
     * @param $entity
     * @param $softDeleted
     * @param $user
     */
    public function __construct($table, $entity, $softDeleted, $user)
    {
        //
        $this->table       = $table;
        $this->entity      = $entity;
        $this->softDeleted = $softDeleted;
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
