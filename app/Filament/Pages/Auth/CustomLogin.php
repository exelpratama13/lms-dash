<?php

namespace App\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;
use Filament\Pages\Auth\Login as BaseLogin;

class CustomLogin extends BaseLogin
{
    protected static string $view = 'filament-panels::pages.auth.login';

    public function authenticate(): ?LoginResponse
    {
        try {
            return parent::authenticate();
        } catch (ValidationException $e) {
            $user = \App\Models\User::where('email', $this->data['email'])->first();

            // If the user exists and the password is correct, but they can't access the panel
            if ($user && \Illuminate\Support\Facades\Hash::check($this->data['password'], $user->password)) {
                // We will check if the user can access the panel
                if (! $user->canAccessPanel(Filament::getPanel())) {
                    throw ValidationException::withMessages([
                        'data.email' => 'Anda tidak memiliki akses ke halaman ini',
                    ]);
                }
            }

            // If the user does not exist or the password is incorrect, throw the original exception
            throw $e;
        }
    }
}
