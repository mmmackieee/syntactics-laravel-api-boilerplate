<?php

use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Models\Auth;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Admin\UserController as AdminUserController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/register', [UserController::class, 'store']);
Route::post('/forgot-password', [UserController::class, 'forgot_password']);
Route::post('/reset-password/{token}', [UserController::class, 'reset_password']);

// 📌 Handle the email verification link
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    // Validate the signature
    if (! URL::hasValidSignature($request)) {
        return response()->json(['message' => 'Invalid or expired verification link.'], 403);
    }

    // Manually load the user
    $user = User::findOrFail($id);

    // Compare the hash
    if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        return response()->json(['message' => 'Invalid verification hash.'], 403);
    }

    // Already verified?
    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.']);
    }

    $user->markEmailAsVerified();

    return response()->json(['message' => 'Email verified successfully.']);
})->middleware('signed')->name('api.verification.verify');

// 📌 Resend the verification email
Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.']);
    }
    if (!$request->user()) {
        return response()->json(['error' => 'User not authenticated'], 401);
    }

    $request->user()->sendEmailVerificationNotification();

    return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

#reserve route for verified emails
Route::middleware(['auth:sanctum', 'verified'])->get('/user-profile', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/auth/update-user-profile-information/', [UserController::class, 'update']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

#RBAC
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('roles/permissions', [RoleController::class, 'permissions']);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', AdminUserController::class);
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
