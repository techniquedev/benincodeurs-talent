<div class="card">
    <h1 class="text-2xl font-semibold tracking-tight text-ink">Nouveau mot de passe</h1>
    <p class="mt-1 text-sm text-muted">Choisis ton nouveau mot de passe.</p>

    <form wire:submit="resetPassword" class="mt-6 space-y-4" novalidate>
        <div>
            <label for="email" class="field-label">E-mail</label>
            <input wire:model="email" id="email" type="email" autocomplete="email" required class="field-input">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="field-label">Nouveau mot de passe</label>
            <input wire:model="password" id="password" type="password" autocomplete="new-password" required class="field-input">
            <p class="mt-1.5 text-xs text-muted">8 caractères minimum, avec au moins une lettre et un chiffre.</p>
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Confirme le mot de passe</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" autocomplete="new-password" required class="field-input">
        </div>

        <button type="submit" class="btn-primary w-full sm:w-full" wire:loading.attr="disabled">
            Enregistrer
        </button>
    </form>
</div>
