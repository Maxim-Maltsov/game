<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $token = session('API-Token');
        // dd($token);
        return view('game', ['token' => $token]);
    }
}
