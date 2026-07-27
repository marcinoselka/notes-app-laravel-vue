<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/notes');

Route::view('/notes', 'notes')->name('notes');

// Auth endpoints live on the 'web' middleware group (not routes/api.php) so that
// StartSession + CSRF verification always run, guaranteeing session-based Sanctum
// SPA authentication works regardless of Origin/Referer detection.
Route::prefix('api')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});
