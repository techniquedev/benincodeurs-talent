<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Propaganistas\LaravelPhone\PhoneNumber;

#[Layout('layouts.guest')]
#[Title('Inscription')]
class Register extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $whatsapp = '+229 ';

    public string $password = '';

    public string $password_confirmation = '';

    public function register()
    {
        $this->email = strtolower(trim($this->email));

        $data = $this->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['required', 'phone:INTERNATIONAL,'.config('talent.phone_country')],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $whatsapp = (new PhoneNumber($data['whatsapp'], config('talent.phone_country')))->formatE164();

        if (User::where('whatsapp', $whatsapp)->exists()) {
            $this->addError('whatsapp', 'Ce numéro WhatsApp est déjà utilisé par un autre compte.');

            return null;
        }

        $user = User::create([
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => $data['email'],
            'whatsapp' => $whatsapp,
            'password' => $data['password'],
        ]);

        event(new Registered($user)); // Envoie l'e-mail de vérification.

        Auth::login($user);
        session()->regenerate();

        return $this->redirectRoute('verification.notice');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
