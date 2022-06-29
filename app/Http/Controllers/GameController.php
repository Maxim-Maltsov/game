<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserCollection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function index()
    {   
        $auth_id = Auth::id();

        $token = session('API-Token');
        
        return view('game', ['token' => $token, 'auth_id' => $auth_id]);
    }
}
