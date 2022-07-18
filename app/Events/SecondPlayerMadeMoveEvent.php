<?php

namespace App\Events;

use App\Http\Resources\GameResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SecondPlayerMadeMoveEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $recipient;
    public $game;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(GameResource $game)
    {
        $this->recipient = $game->firstPlayer;
        $this->game = $game;
    }

    public function broadcastWith()
    {   
        return [
            
            'message' => $this->game->secondPlayer->name . ' made a move.',
            'game' => $this->game,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('privateChannelFor.' . $this->recipient->id);
    }
}
