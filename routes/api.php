
<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard exclusivo para administradores.
    Route::get('/admin/dashboard', function (Request $request) {
        return response()->json([
            'message' => 'Bienvenido al dashboard administrativo',
            'user' => $request->user()->name,
        ]);
    })->middleware('can:access-admin-dashboard');

    // Dashboard exclusivo para profesores.
    Route::get('/profesor/dashboard', function (Request $request) {
        return response()->json([
            'message' => 'Bienvenido al dashboard de profesores',
            'user' => $request->user()->name,
        ]);
    })->middleware('can:access-professor-dashboard');

});
