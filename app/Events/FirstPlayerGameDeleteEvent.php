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

class FirstPlayerGameDeleteEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // ОТПРАВЛЯЕТСЯ ПЕРВОМУ ИГРОКУ - СРАБАТЫВАЕ ПРИ УДАЛЕНИИ ИГРЫ ВТОРЫМ ИГРОКОМ!!!!
    
    public $recipient;
    public $game; 

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(GameResource $game)
    {
        $this->recipient = $game->secondPlayer;
        $this->game = $game;
    }

        /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {   
        
        return [
            
            'message' => $this->game->firstPlayer->name . " canceled the game.",
            'info' => true,
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
