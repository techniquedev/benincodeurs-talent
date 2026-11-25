<div class="card">
    <h1 class="text-2xl font-semibold tracking-tight text-ink">Mot de passe oublié</h1>
    <p class="mt-1 text-sm text-muted">Indique ton e-mail : tu recevras un lien pour choisir un nouveau mot de passe.</p>

    @if ($status)
        <div class="alert mt-5" role="status">{{ $status }}</div>
    @endif

    <form wire:submit="sendLink" class="mt-6 space-y-4" novalidate>
        <div>
            <label for="email" class="field-label">E-mail</label>
            <input wire:model="email" id="email" type="email" autocomplete="email" inputmode="email" required autofocus class="field-input">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full sm:w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="sendLink">Envoyer le lien</span>
            <span wire:loading wire:target="sendLink">Envoi…</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        <a href="{{ route('login') }}" class="link" wire:navigate>Retour à la connexion</a>
    </p>
</div>
