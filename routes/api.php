
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Middleware\AdminMiddleware;

// LOGIN
Route::post('/login', [AuthController::class, 'login']);

// PROTECTED ROUTES
Route::middleware('auth:sanctum')->group(function () {

    // GET USER
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout']);

    // VIEW STUDENTS
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}', [StudentController::class, 'show']);

    // ADMIN ONLY
    Route::middleware([AdminMiddleware::class])->group(function () {

        Route::post('/students', [StudentController::class, 'store']);

        Route::put('/students/{id}', [StudentController::class, 'update']);

        Route::delete('/students/{id}', [StudentController::class, 'destroy']);

    });
});
