<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiTokenServisece extends Model
{
    use HasFactory;

    public static function makeToken($user) 
    {
        $token = $user->createToken('API-Token')->plainTextToken;
        session(['API-Token' => $token]);
    }

    public static function deleteToken($user)
    {
        $user->tokens()->delete();
    }
}
