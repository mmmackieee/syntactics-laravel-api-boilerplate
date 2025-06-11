<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\ResetPasswordViewResponse;

class ResetPasswordResponse implements ResetPasswordViewResponse
{
    public function toResponse($request)
    {
        abort(404, 'Password reset view is not available.');
    }
}