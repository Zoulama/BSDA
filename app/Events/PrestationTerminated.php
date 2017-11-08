<?php

namespace Provisioning\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Provisioning\ComptaPrestation as Prestation;

class PrestationTerminated
{
    use InteractsWithSockets, SerializesModels;

    public $prestation;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Prestation $prestation)
    {
        $this->prestation = $prestation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
