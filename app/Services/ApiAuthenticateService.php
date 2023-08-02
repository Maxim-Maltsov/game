<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
*  Service for managing API tokens of your users. Uses 'Sunctum'.
*/
class ApiAuthenticateService extends Model
{
    use HasFactory;

    /**
     * Issues an API token to the user. 
     */
    public static function makeToken($user) :void
    {
        $token = $user->createToken('API-Token')->plainTextToken;
        session(['API-Token' => $token]);
    }

    /**
     * Revoke all API tokens the user has.
     */
    public static function deleteTokens($user) :void
    {
        $user->tokens()->delete();
    }
}
