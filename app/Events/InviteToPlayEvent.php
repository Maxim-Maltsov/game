<?php

namespace App\Events;

use App\Http\Resources\UserResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InviteToPlayEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $player_1 = null;
    public $player_2 = null;
    public $game = null;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($player_1, $player_2, $game)
    {
        $this->player_1 = $player_1;
        $this->player_2 = $player_2;
        $this->game = $game;
    }

    // public function broadcastWith(): array
    // {
    //     return [
    //         'player_1' => UserResource::make($this->player_1),
    //         'player_2' => UserResource::make($this->player_2),
    //         'game' => $this->game
    //     ];
    // }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('privateMessageFor.' . $this->player_2->id);
    }
}
