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

class GameFinishEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $firstRecipient;
    public $secondRecipient;
    public $game;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public function __construct(GameResource $game)
    {   
        $this->firstRecipient = $game->firstPlayer;
        $this->secondRecipient = $game->secondPlayer;
        $this->game = $game;
    }

    public function broadcastWith()
    {   
        $winnedPlayer = $this->game->winnedPlayer;

        return [
            
            'game' => $this->game,
            'message' => "The game is over. $winnedPlayer->name wins the game.",
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [

            new PrivateChannel('privateChannelFor.'. $this->firstRecipient->id),
            new PrivateChannel('privateChannelFor.'. $this->secondRecipient->id),
        ];

    }
}
