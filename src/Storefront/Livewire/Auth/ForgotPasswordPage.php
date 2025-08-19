<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Livewire\Component;

class ForgotPasswordPage extends Component
{
    public string $email = '';

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.auth.forgot-password')
            ->title(__('Recuperar contraseña'));
    }

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}
