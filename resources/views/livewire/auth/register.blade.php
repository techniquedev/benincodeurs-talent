<div class="card">
    <h1 class="text-2xl font-extrabold tracking-tight text-brand">Crée ton compte</h1>
    <p class="mt-1 text-sm text-gray-600">Rejoins l'annuaire des membres de la communauté VIP Bénin Codeurs.</p>

    <form wire:submit="register" class="mt-6 space-y-4" novalidate>
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
            <label for="email" class="field-label">E-mail</label>
            <input wire:model="email" id="email" type="email" autocomplete="email" inputmode="email" required class="field-input">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="whatsapp" class="field-label">Numéro WhatsApp</label>
            <input wire:model="whatsapp" id="whatsapp" type="tel" autocomplete="tel" inputmode="tel" required class="field-input" placeholder="+229 01 90 00 00 00" aria-describedby="whatsapp-help">
            <p id="whatsapp-help" class="mt-1.5 text-xs text-gray-500">Format international. +229 par défaut, change l'indicatif si besoin.</p>
            @error('whatsapp') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="field-label">Mot de passe</label>
            <input wire:model="password" id="password" type="password" autocomplete="new-password" required class="field-input" aria-describedby="password-help">
            <p id="password-help" class="mt-1.5 text-xs text-gray-500">8 caractères minimum, avec au moins une lettre et un chiffre.</p>
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Confirme le mot de passe</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" autocomplete="new-password" required class="field-input">
        </div>

        <button type="submit" class="btn-primary w-full sm:w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="register">Créer mon compte</span>
            <span wire:loading wire:target="register">Création…</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Déjà inscrit ? <a href="{{ route('login') }}" class="link" wire:navigate>Connecte-toi</a>
    </p>
</div>
