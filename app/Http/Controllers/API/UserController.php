<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = app(CreateNewUser::class)->create($request->all());
        $token = $user->createToken('api-token')->plainTextToken;
        $user->sendEmailVerificationNotification();
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function forgot_password(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function reset_password(Request $request, $token)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                // Delegate to Fortify's built-in class
                app(ResetUserPassword::class)->reset($user, [
                    'password' => $request->input('password'),
                    'password_confirmation' => $request->input('password_confirmation')
                ]);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = Auth::user(); // or auth('sanctum')->user()

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        app(UpdateUserProfileInformation::class)->update($user, $request);

        // Ensure fresh is called on a valid model
        $updatedUser = User::find($user->id);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $updatedUser,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
