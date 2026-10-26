<?php

use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/students', [StudentController::class, 'index']);
Route::post('/students', [StudentController::class, 'store']);
Route::get('/students/{id}', [StudentController::class, 'show'])->whereNumber('id');
Route::put('/students/{id}', [StudentController::class, 'update'])->whereNumber('id');
Route::delete('/students/{id}', [StudentController::class, 'destroy'])->whereNumber('id');