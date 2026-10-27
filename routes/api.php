<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);


Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

  
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}', [StudentController::class, 'show'])->whereNumber('id');

  
    Route::middleware('role:admin')->group(function () {
        Route::post('/students', [StudentController::class, 'store']);
        Route::put('/students/{id}', [StudentController::class, 'update'])->whereNumber('id');
        Route::delete('/students/{id}', [StudentController::class, 'destroy'])->whereNumber('id');
    });
});