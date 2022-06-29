<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use PhpParser\Node\Expr\Cast\Int_;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Online status.
    const OFFLINE = 0;
    const ONLINE = 1;


    // Game status.
    const WAITING_PLAYER = 0;
    const FREE = 1;
    const PLAYING = 2;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    
    public static function getOnlineUsersPaginate($amount)
    {
        $users = User::where('online_status', User::ONLINE)->paginate($amount);
        
        return $users;
    }

    public function canPlay()
    {   
        // Проверить учавствует ли пользователь в игре 
        // вкачестве первого или второго игрока,
        // если да вернуть false, если нет true

        // $game = Game::where('player_1', Auth::id())->first();
        
        // if ($game instanceof Game) {

        //     return false;
        // }

        // $game = Game::where('player_2', Auth::id())->first();

        // if ($game instanceof Game) {

        //     return false;
        // }

        return true;
    }

}
