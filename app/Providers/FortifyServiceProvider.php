<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\ResetPasswordResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Fortify responses to return JSON for API usage
        $this->app->bind(LoginViewResponse::class, function () {
            return new class implements LoginViewResponse {
                public function toResponse($request)
                {
                    return response()->json(['message' => 'Login view not available.'], 401);
                }
            };
        });

        $this->app->bind(RegisterViewResponse::class, function () {
            return new class implements RegisterViewResponse {
                public function toResponse($request)
                {
                    return response()->json(['message' => 'Register view not available.'], 404);
                }
            };
        });

        $this->app->bind(VerifyEmailViewResponse::class, function () {
            return new class implements VerifyEmailViewResponse {
                public function toResponse($request)
                {
                    return response()->json(['message' => 'Email verification view not available.'], 200);
                }
            };
        });

        $this->app->bind(TwoFactorChallengeViewResponse::class, function () {
            return new class implements TwoFactorChallengeViewResponse {
                public function toResponse($request)
                {
                    return response()->json(['message' => '2FA challenge view not available.'], 200);
                }
            };
        });

        $this->app->bind(RequestPasswordResetLinkViewResponse::class, function () {
            return new class implements RequestPasswordResetLinkViewResponse {
                public function toResponse($request)
                {
                    return response()->json(['message' => 'Password reset request view not available.'], 404);
                }
            };
        });

        $this->app->bind(ResetPasswordViewResponse::class, ResetPasswordResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use custom action classes
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Ignore Blade routes since this is API-based
        Fortify::ignoreRoutes();

        // Custom authentication logic (for API)
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            return $user && Hash::check($request->password, $user->password) ? $user : null;
        });

        // Rate limiting for login
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::lower($request->input(Fortify::username())) . '|' . $request->ip()
            );
        });

        // Rate limiting for 2FA
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
