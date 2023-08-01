<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Controls the display of user interface elements.
 */
class GameFieldManagementService extends Model
{
    use HasFactory;

    /**
     * Determines whether to show a block with information about waiting for the second player.
     */
    public static function needShowPlayerWaitingBlock(): bool
    {   
        // Перенести запрос в класс "gameRepository". Получаем игру в которой игровой статус 1-го игрока равен 'WAITING_PLAYER'. 1-ый игрок ожидает 2-го игрока. 
        $game = Game::where('status', Game::WAITING_PLAYER)
                    ->where('player_1', Auth::id())
                    ->first();
        
        if (!$game) {
            return false;
        }

        return true;
    }

    /**
     * Determines whether to show a block with an offer to play the game to the second player.
     */
    public static function needShowBlockWithOfferToPlay(): bool
    {   
        // Перенести запрос в класс "gameRepository". Получаем игру в которой игровой статус 2-го игрока равен 'WAITING_PLAYER'. 2-й игрок ещё не ответил на предложения 1-го принять участие в игре.
        $game = Game::where('status', Game::WAITING_PLAYER)
                    ->where('player_2', Auth::id())
                    ->first();
       
        if (!$game) {       
            return false;
        }

        return true;
    }

    /**
     * Determines whether to show the block with the game field to the first and second players.
     */
    public static function needShowGameFieldBlock(): bool
    {   
        // Перенести запрос в класс "gameRepository". Получаем последнюю игру со статусом 'IN_PROCESS' в которой аутентифицированный пользователь является 1-ым или 2-ым игроком. 
        $game = Game::where('status', Game::IN_PROCESS)
                    ->where(function ($query)  {
                        $query->where('player_1', Auth::id());
                        $query->orWhere('player_2', Auth::id());
                    })->latest()->first();
                
        if (!$game) {       
            return false;
        }

        return true;
    }


    public static function needShowButtonLeaveGame(): bool
    {   
        // Перенести запрос в класс "gameRepository". Получаем последнюю игру со статусом 'IN_PROCESS' в которой аутентифицированный пользователь является 1-ым или 2-ым игроком. 
        $game = Game::where('status', Game::IN_PROCESS)
                    ->where(function ($query)  {
                        $query->where('player_1', Auth::id());
                        $query->orWhere('player_2', Auth::id());
                    })->latest()->first();

        if (!$game) {       
            return false;
        }

        return true;
    }


}
