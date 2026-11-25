<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Mot de passe oublié')]
class ForgotPassword extends Component
{
    public string $email = '';

    public ?string $status = null;

    public function sendLink(): void
    {
        $this->validate(['email' => ['required', 'string', 'email']]);

        $result = Password::sendResetLink(['email' => strtolower(trim($this->email))]);

        if ($result === Password::RESET_THROTTLED) {
            $this->addError('email', __($result));

            return;
        }

        // Même message que le compte existe ou non : on ne révèle pas les adresses inscrites.
        $this->status = __(Password::RESET_LINK_SENT);
        $this->reset('email');
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
