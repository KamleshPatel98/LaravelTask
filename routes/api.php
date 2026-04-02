<?php

use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('plans', PlanController::class);
Route::apiResource('users', UserController::class);