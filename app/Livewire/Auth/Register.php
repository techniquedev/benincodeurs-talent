<?php

namespace App\Livewire\Auth;

use App\Enums\UserStatus;
use App\Enums\VipStatus;
use App\Models\User;
use App\Notifications\RegistrationCodeNotification;
use App\Support\VipMembershipChecker;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Propaganistas\LaravelPhone\PhoneNumber;
use Throwable;

/**
 * Inscription en 4 étapes : e-mail vérifié auprès de l'API VIP, code à 6 chiffres,
 * informations (pré-remplies par l'API), mot de passe.
 *
 * L'état de confiance (e-mail, données VIP, code, étape franchie) vit en session côté serveur :
 * les propriétés publiques ne servent qu'à l'affichage et aux saisies.
 */
#[Layout('layouts.guest')]
#[Title('Inscription')]
class Register extends Component
{
    private const SESSION_KEY = 'registration';

    public const TOTAL_STEPS = 4;

    #[Locked]
    public int $step = 1;

    /** Issue de la vérification VIP affichée à l'étape 1 : not_found, expired ou unavailable. */
    #[Locked]
    public ?string $vipOutcome = null;

    public string $email = '';

    public string $code = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $whatsapp = '';

    public string $password = '';

    public string $password_confirmation = '';

    /** Reprend l'inscription en cours (rechargement de page) à la bonne étape. */
    public function mount(): void
    {
        $registration = $this->registration();

        if (! isset($registration['email'])) {
            return;
        }

        $this->email = $registration['email'];
        $this->step = 2;

        if ($registration['email_verified'] ?? false) {
            $this->fillInformation($registration);
            $this->step = isset($registration['info']) ? 4 : 3;
        }
    }

    /** Étape 1 : l'e-mail doit correspondre à une adhésion VIP active. */
    public function checkEmail(VipMembershipChecker $checker): void
    {
        $this->vipOutcome = null;
        $this->email = mb_strtolower(trim($this->email));

        $this->validate(['email' => ['required', 'string', 'email', 'max:255']]);

        $limiterKey = 'register-email:'.request()->ip();

        if (RateLimiter::tooManyAttempts($limiterKey, 10)) {
            $this->addError('email', 'Trop de tentatives. Réessaie dans '.RateLimiter::availableIn($limiterKey).' secondes.');

            return;
        }

        RateLimiter::hit($limiterKey, 60);

        if (User::where('email', $this->email)->exists()) {
            $this->addError('email', 'Un compte existe déjà avec cette adresse. Connecte-toi pour y accéder.');

            return;
        }

        $membership = $checker->check($this->email);

        if ($membership === null) {
            $this->vipOutcome = 'unavailable';

            return;
        }

        if (! $membership->isActive()) {
            $this->vipOutcome = $membership->status === VipStatus::Expired ? 'expired' : 'not_found';

            return;
        }

        session()->put(self::SESSION_KEY, [
            'email' => $this->email,
            'vip' => ['first_name' => $membership->firstName, 'whatsapp' => $membership->whatsapp],
            'email_verified' => false,
        ]);

        $this->code = '';
        $this->step = 2;
        $this->sendCode();
    }

    /** Étape 2 : renvoi du code, limité à un envoi par minute. */
    public function resendCode(): void
    {
        if ($this->step !== 2 || ! isset($this->registration()['email'])) {
            $this->restart();

            return;
        }

        if ($this->sendCode()) {
            session()->flash('status', 'Un nouveau code vient de t\'être envoyé.');
        }
    }

    /** Étape 2 : 15 minutes de validité, 5 essais par code. */
    public function verifyCode(): void
    {
        $registration = $this->registration();

        if ($this->step !== 2 || ! isset($registration['email'])) {
            $this->restart();

            return;
        }

        $this->code = preg_replace('/\D/', '', $this->code) ?? '';
        $this->validate(['code' => ['required', 'digits:6']], [
            'code.required' => 'Saisis le code reçu par e-mail.',
            'code.digits' => 'Le code contient 6 chiffres.',
        ]);

        $maxAttempts = (int) config('talent.registration.code_max_attempts');

        if (! isset($registration['code_hash']) || now()->timestamp > $registration['code_expires_at']) {
            $this->addError('code', 'Ce code a expiré. Demande un nouveau code.');

            return;
        }

        if ($registration['code_attempts'] >= $maxAttempts) {
            $this->addError('code', 'Trop d\'essais avec ce code. Demande un nouveau code.');

            return;
        }

        if (! Hash::check($this->code, $registration['code_hash'])) {
            $registration['code_attempts']++;
            session()->put(self::SESSION_KEY, $registration);

            $remaining = $maxAttempts - $registration['code_attempts'];
            $this->addError('code', $remaining > 0
                ? "Code incorrect. Il te reste $remaining essai".($remaining > 1 ? 's' : '').'.'
                : 'Code incorrect. Demande un nouveau code.');

            return;
        }

        unset($registration['code_hash'], $registration['code_expires_at'], $registration['code_attempts']);
        $registration['email_verified'] = true;
        session()->put(self::SESSION_KEY, $registration);

        $this->code = '';
        $this->fillInformation($registration);
        $this->step = 3;
    }

    /** Étape 2, e-mail déjà confirmé (après un retour) : on repasse à l'étape 3. */
    public function continueToInformation(): void
    {
        if (! ($this->registration()['email_verified'] ?? false)) {
            return;
        }

        $this->step = 3;
    }

    /** Étape 3 : prénom, nom, WhatsApp. L'e-mail est verrouillé. */
    public function saveInformation(): void
    {
        $registration = $this->registration();

        if (! ($registration['email_verified'] ?? false)) {
            $this->restart();

            return;
        }

        $data = $this->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'whatsapp' => ['required', 'phone:INTERNATIONAL,'.config('talent.phone_country')],
        ]);

        $whatsapp = (new PhoneNumber($data['whatsapp'], config('talent.phone_country')))->formatE164();

        if (User::where('whatsapp', $whatsapp)->exists()) {
            $this->addError('whatsapp', 'Ce numéro WhatsApp est déjà utilisé par un autre compte.');

            return;
        }

        $registration['info'] = [
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'whatsapp' => $whatsapp,
        ];
        session()->put(self::SESSION_KEY, $registration);

        $this->step = 4;
    }

    /** Étape 4 : création du compte, directement validé et e-mail vérifié. */
    public function register()
    {
        $registration = $this->registration();

        if (! ($registration['email_verified'] ?? false) || ! isset($registration['info'])) {
            $this->restart();

            return null;
        }

        $this->validate(['password' => ['required', 'string', 'confirmed', Password::defaults()]]);

        $info = $registration['info'];

        if (User::where('email', $registration['email'])->exists()) {
            $this->restart();
            $this->addError('email', 'Un compte existe déjà avec cette adresse. Connecte-toi pour y accéder.');

            return null;
        }

        $user = new User;
        $user->forceFill([
            'first_name' => $info['first_name'],
            'last_name' => $info['last_name'],
            'email' => $registration['email'],
            'whatsapp' => $info['whatsapp'],
            'password' => $this->password,
            'status' => UserStatus::Approved,
            'approved_at' => now(),
            'email_verified_at' => now(),
            'vip_verification_method' => 'api',
            'vip_verified_at' => now(),
        ]);

        try {
            $user->save();
        } catch (UniqueConstraintViolationException) {
            $this->step = 3;
            $this->addError('whatsapp', 'Ce numéro WhatsApp ou cet e-mail est déjà utilisé par un autre compte.');

            return null;
        }

        session()->forget(self::SESSION_KEY);

        Auth::login($user);
        session()->regenerate();

        return $this->redirectRoute('profile.edit');
    }

    /** Retour à l'étape précédente. Revenir à l'étape 1 annule la vérification en cours. */
    public function back(): void
    {
        match ($this->step) {
            2 => $this->restart(keepEmail: true),
            3, 4 => $this->step--,
            default => null,
        };

        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.auth.register', [
            'totalSteps' => self::TOTAL_STEPS,
            'vipJoinUrl' => config('talent.vip_join_url'),
            'emailVerified' => (bool) ($this->registration()['email_verified'] ?? false),
        ]);
    }

    /** Génère, enregistre (haché) et envoie un nouveau code. Renvoie false si l'envoi est limité ou échoue. */
    private function sendCode(): bool
    {
        $registration = $this->registration();
        $resendSeconds = (int) config('talent.registration.code_resend_seconds');
        $limiterKey = 'register-code:'.sha1($registration['email']);

        if (RateLimiter::tooManyAttempts($limiterKey, 1)) {
            $this->addError('code', 'Patiente encore '.RateLimiter::availableIn($limiterKey).' secondes avant de demander un nouveau code.');

            return false;
        }

        RateLimiter::hit($limiterKey, $resendSeconds);

        $code = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);

        $registration['code_hash'] = Hash::make($code);
        $registration['code_expires_at'] = now()->addMinutes((int) config('talent.registration.code_ttl_minutes'))->timestamp;
        $registration['code_attempts'] = 0;
        session()->put(self::SESSION_KEY, $registration);

        try {
            Notification::route('mail', $registration['email'])->notify(new RegistrationCodeNotification($code));
        } catch (Throwable $exception) {
            Log::error('Envoi du code d\'inscription impossible.', ['error' => $exception->getMessage()]);
            RateLimiter::clear($limiterKey);
            $this->addError('code', 'L\'e-mail n\'a pas pu être envoyé. Réessaie dans un instant.');

            return false;
        }

        return true;
    }

    /** Pré-remplit l'étape 3 : saisies déjà faites, sinon données de l'API VIP. */
    private function fillInformation(array $registration): void
    {
        $info = $registration['info'] ?? null;

        $this->first_name = $info['first_name'] ?? $registration['vip']['first_name'] ?? '';
        $this->last_name = $info['last_name'] ?? '';
        $this->whatsapp = $info['whatsapp'] ?? $this->formatWhatsapp($registration['vip']['whatsapp'] ?? null);
    }

    private function formatWhatsapp(?string $number): string
    {
        if ($number === null) {
            return '+229 ';
        }

        try {
            return (new PhoneNumber($number, config('talent.phone_country')))->formatInternational();
        } catch (Throwable) {
            return $number;
        }
    }

    private function restart(bool $keepEmail = false): void
    {
        session()->forget(self::SESSION_KEY);

        $this->reset('code', 'first_name', 'last_name', 'whatsapp', 'password', 'password_confirmation', 'vipOutcome');

        if (! $keepEmail) {
            $this->reset('email');
        }

        $this->step = 1;
    }

    /** @return array{email?: string, vip?: array{first_name: ?string, whatsapp: ?string}, email_verified?: bool, code_hash?: string, code_expires_at?: int, code_attempts?: int, info?: array{first_name: string, last_name: string, whatsapp: string}} */
    private function registration(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }
}
