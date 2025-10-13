<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/register', [AdminController::class, 'register']);
Route::post('/login', [AdminController::class, 'login']);
Route::post('/student/register', [StudentController::class, 'register']);
Route::post('/student/login', [StudentController::class, 'login']);
