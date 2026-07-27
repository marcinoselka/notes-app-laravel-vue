<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/notes');

Route::view('/login', 'login')->name('login');

Route::middleware('auth')->group(function () {
    Route::view('/notes', 'notes')->name('notes');

    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});

// Auth endpoints live on the 'web' middleware group (not routes/api.php) so that
// StartSession + CSRF verification always run, guaranteeing session-based Sanctum
// SPA authentication works regardless of Origin/Referer detection. Used by the
// Vue AuthForm on the /login page.
Route::prefix('api')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});
