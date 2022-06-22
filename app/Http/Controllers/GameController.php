<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        
        dd(Carbon::now()->timestamp);
        // dd(Carbon::now()->addMinute(1));
        
        return view('game');
    }
}
