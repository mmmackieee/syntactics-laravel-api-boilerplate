<?php

namespace App\Actions\Fortify;

use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return [
            'required',
            'string',
            Password::min(8) // or Password::default()
                ->mixedCase()       // requires both upper and lower case letters
                ->letters()         // requires at least one letter (redundant if mixedCase is used, but fine)
                ->symbols()         // requires at least one special character
                ->numbers()         // optional: require at least one number
                ->uncompromised(),  // optional: checks if password was exposed in a data leak
            'confirmed',
        ];
    }
}
