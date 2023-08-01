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
        
        if ($game->isEmpty()) {
            return false;
        }

        return true;
    }
}
