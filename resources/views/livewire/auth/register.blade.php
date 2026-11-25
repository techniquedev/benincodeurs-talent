@php
    $stepTitles = [1 => 'Ton e-mail', 2 => 'Code de confirmation', 3 => 'Mes informations', 4 => 'Mot de passe'];
@endphp

<div class="card">
    {{-- Progression --}}
    <div>
        <div class="flex items-center justify-between gap-3 text-sm">
            <p class="font-semibold text-violet">Étape {{ $step }} sur {{ $totalSteps }}</p>
            <p class="text-muted">{{ $stepTitles[$step] }}</p>
        </div>
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-line" role="progressbar" aria-label="Progression de l'inscription"
             aria-valuemin="1" aria-valuemax="{{ $totalSteps }}" aria-valuenow="{{ $step }}">
            <div class="h-full rounded-full bg-violet transition-all motion-reduce:transition-none duration-300" style="width: {{ $step / $totalSteps * 100 }}%"></div>
        </div>
    </div>

    @if ($step > 1)
        <button type="button" wire:click="back" class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-ink">
            <span aria-hidden="true">←</span> Retour
        </button>
    @endif

    {{-- Étape 1 : e-mail --}}
    @if ($step === 1)
        <h1 class="mt-6 text-2xl font-semibold tracking-tight text-ink">Crée ton compte</h1>
        <p class="mt-1 text-sm text-muted">Le Talent Board est réservé aux membres VIP de la communauté Bénin Codeurs. Commence par l'adresse e-mail de ton adhésion.</p>

        <form wire:submit="checkEmail" class="mt-6 space-y-4" novalidate>
            <div>
                <label for="email" class="field-label">E-mail</label>
                <input wire:model="email" id="email" type="email" autocomplete="email" inputmode="email" required autofocus class="field-input">
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            @if ($vipOutcome === 'not_found')
                <div class="rounded-lg border border-violet/15 bg-violet/5 p-4 text-sm text-ink" role="status">
                    <p class="font-semibold text-ink">Le Talent Board est réservé aux membres VIP</p>
                    <p class="mt-1">Nous n'avons pas trouvé d'adhésion VIP avec cette adresse. Si tu as utilisé une autre adresse pour la communauté, essaie avec celle-là. Sinon, on serait ravis de t'accueillir parmi nous !</p>
                    <a href="{{ $vipJoinUrl }}" target="_blank" rel="noopener" class="btn-primary mt-4 w-full">Rejoindre la communauté VIP</a>
                </div>
            @elseif ($vipOutcome === 'expired')
                <div class="rounded-lg border border-violet/15 bg-violet/5 p-4 text-sm text-ink" role="status">
                    <p class="font-bold">Ton adhésion VIP a expiré</p>
                    <p class="mt-1">Renouvelle-la pour accéder au Talent Board : tu pourras ensuite créer ton compte avec cette adresse.</p>
                    <a href="{{ $vipJoinUrl }}" target="_blank" rel="noopener" class="btn-primary mt-4 w-full">Rejoindre la communauté VIP</a>
                </div>
            @elseif ($vipOutcome === 'unavailable')
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <p class="font-bold">Vérification impossible pour le moment</p>
                    <p class="mt-1">Nous n'arrivons pas à joindre le service de la communauté. Réessaie dans quelques minutes.</p>
                </div>
            @endif

            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="checkEmail">
                <span wire:loading.remove wire:target="checkEmail">Continuer</span>
                <span wire:loading wire:target="checkEmail">Vérification…</span>
            </button>
        </form>
    @endif

    {{-- Étape 2 : code reçu par e-mail --}}
    @if ($step === 2)
        <h1 class="mt-4 text-2xl font-semibold tracking-tight text-ink">Confirme ton e-mail</h1>

        @if ($emailVerified)
            <p class="mt-1 text-sm text-muted">L'adresse <span class="font-semibold text-ink">{{ $email }}</span> est déjà confirmée.</p>
            <button type="button" wire:click="continueToInformation" class="btn-primary mt-6 w-full">Continuer</button>
        @else
            <p class="mt-1 text-sm text-muted">
                Nous avons envoyé un code à 6 chiffres à <span class="font-semibold text-ink">{{ $email }}</span>.
                Il est valable {{ config('talent.registration.code_ttl_minutes') }} minutes.
            </p>

            @if (session('status'))
                <p class="alert mt-4" role="status">{{ session('status') }}</p>
            @endif

            <form wire:submit="verifyCode" class="mt-6 space-y-4" novalidate>
                <div>
                    <label for="code" class="field-label">Code de confirmation</label>
                    <input wire:model="code" id="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}"
                           required autofocus class="field-input text-center text-2xl font-bold tracking-[0.5em]" placeholder="••••••">
                    @error('code') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="verifyCode">
                    <span wire:loading.remove wire:target="verifyCode">Vérifier le code</span>
                    <span wire:loading wire:target="verifyCode">Vérification…</span>
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-muted">
                Pas reçu ? Regarde dans tes spams, ou
                <button type="button" wire:click="resendCode" wire:loading.attr="disabled" wire:target="resendCode" class="link">renvoie le code</button>.
            </p>
        @endif
    @endif

    {{-- Étape 3 : informations --}}
    @if ($step === 3)
        <h1 class="mt-4 text-2xl font-semibold tracking-tight text-ink">Mes informations</h1>
        <p class="mt-1 text-sm text-muted">Vérifie et complète ce que nous connaissons déjà de toi.</p>

        <form wire:submit="saveInformation" class="mt-6 space-y-4" novalidate>
            <div>
                <label for="locked_email" class="field-label">E-mail</label>
                <input id="locked_email" type="email" value="{{ $email }}" disabled class="field-input cursor-not-allowed bg-line text-muted">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="first_name" class="field-label">Prénom</label>
                    <input wire:model="first_name" id="first_name" type="text" autocomplete="given-name" required class="field-input">
                    @error('first_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="last_name" class="field-label">Nom</label>
                    <input wire:model="last_name" id="last_name" type="text" autocomplete="family-name" required class="field-input">
                    @error('last_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="whatsapp" class="field-label">Numéro WhatsApp</label>
                <input wire:model="whatsapp" id="whatsapp" type="tel" autocomplete="tel" inputmode="tel" required class="field-input" placeholder="+229 01 90 00 00 00" aria-describedby="whatsapp-help">
                <p id="whatsapp-help" class="mt-1.5 text-xs text-muted">Format international. +229 par défaut, change l'indicatif si besoin.</p>
                @error('whatsapp') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="saveInformation">Continuer</button>
        </form>
    @endif

    {{-- Étape 4 : mot de passe --}}
    @if ($step === 4)
        <h1 class="mt-4 text-2xl font-semibold tracking-tight text-ink">Choisis ton mot de passe</h1>
        <p class="mt-1 text-sm text-muted">Dernière étape avant de remplir ton profil.</p>

        <form wire:submit="register" class="mt-6 space-y-4" novalidate>
            <div>
                <label for="password" class="field-label">Mot de passe</label>
                <input wire:model="password" id="password" type="password" autocomplete="new-password" required autofocus class="field-input" aria-describedby="password-help">
                <p id="password-help" class="mt-1.5 text-xs text-muted">8 caractères minimum, avec au moins une lettre et un chiffre.</p>
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="field-label">Confirme le mot de passe</label>
                <input wire:model="password_confirmation" id="password_confirmation" type="password" autocomplete="new-password" required class="field-input">
            </div>

            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="register">
                <span wire:loading.remove wire:target="register">Créer mon compte</span>
                <span wire:loading wire:target="register">Création…</span>
            </button>
        </form>
    @endif

    <p class="mt-6 text-center text-sm text-muted">
        Déjà inscrit ? <a href="{{ route('login') }}" class="link" wire:navigate>Connecte-toi</a>
    </p>
</div>
