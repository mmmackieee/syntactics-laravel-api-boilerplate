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
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Fortify;
class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ResetPasswordViewResponse::class, ResetPasswordResponse::class, ["toResponse"]);

    // Do the same for other view contracts:
    $this->app->bind(\Laravel\Fortify\Contracts\LoginViewResponse::class, fn () => abort(404));
    $this->app->bind(\Laravel\Fortify\Contracts\RegisterViewResponse::class, fn () => abort(404));
    $this->app->bind(\Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse::class, fn () => abort(404));
    $this->app->bind(\Laravel\Fortify\Contracts\VerifyEmailViewResponse::class, fn () => abort(404));
    $this->app->bind(\Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse::class, fn () => abort(404));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // ❌ No Blade views – we're using API responses
        Fortify::loginView(fn() => abort(404));
        Fortify::registerView(fn() => abort(404));
        Fortify::requestPasswordResetLinkView(fn() => abort(404));
        Fortify::resetPasswordView(fn() => abort(404));
        Fortify::verifyEmailView(fn() => abort(404));
        Fortify::twoFactorChallengeView(fn() => abort(404));

        // ✅ Custom authentication logic using `Auth::attempt()`
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            return null;
        });

        // 🛡 Rate Limiting
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
