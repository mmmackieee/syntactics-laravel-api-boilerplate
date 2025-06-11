<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/register', [UserController::class, 'store']);
Route::post('/forgot-password', [UserController::class, 'forgot_password']);
Route::post('/reset-password/{token}', [UserController::class, 'reset_password']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin-only', function () {
    return response()->json(['message' => 'You are an admin']);
});

Route::middleware(['auth:sanctum', 'permission:view dashboard'])->get('/dashboard', function () {
    return response()->json(['message' => 'You have permission to view the dashboard']);
});

Route::middleware(['auth:sanctum', 'role_or_permission:admin|edit articles'])->get('/edit', function () {
    return response()->json(['message' => 'Access granted']);
});
