<?php

namespace App\Filament\Auth;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\AuthUiEnhancerLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends AuthUiEnhancerLogin
{
    /**
     * Append the app name to the default sign in heading.
     */
    public function getHeading(): string|Htmlable|null
    {
        $heading = parent::getHeading();

        return $heading === null ? null : $heading.' - HRIS';
    }
}
