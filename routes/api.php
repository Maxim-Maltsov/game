<?php
use App\Http\Controllers\Api\v1\CancelGameInviteController;
use App\Http\Controllers\Api\v1\RejectGameInviteContriller;
use App\Http\Controllers\Api\v1\GameController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware(['auth:sanctum', 'last-activity'])->group(function () {

    Route::apiResources([

        'users' => UserController::class,
        'games' => GameController::class,
    ]);

});


Route::middleware(['auth:sanctum', 'last-activity'])->group(function () {

    Route::post('invite-play', [GameController::class, 'inviteToPlay'])->name('invite-play');
    // Route::delete('cancel-invite/{game}', [GameController::class, 'cancelInvite'])->name('cancel-invite');
    Route::delete('cancel-invite/{game}', [CancelGameInviteController::class, '__invoke'])->name('cancel-invite');

    Route::put('accept-invite/{game}', [GameController::class, 'acceptInvite'])->name('accept-invite');
    // Route::delete('reject-invite/{game}', [GameController::class, 'rejectInvite'])->name('reject-invite');
    Route::delete('reject-invite/{game}', [RejectGameInviteContriller::class, '__invoke'])->name('reject-invite');

    Route::put('leave-game/{game}', [GameController::class, 'leaveGame'])->name('leave-game');
    Route::post('make-move', [GameController::class, 'makeMove'])->name('make-move');

    Route::get('init-game', [GameController::class, 'initGame'])->name('init-game');
});
