<div class="card">
    <h1 class="text-2xl font-semibold tracking-tight text-ink">Connexion</h1>
    <p class="mt-1 text-sm text-muted">Accède à ton espace membre du Talent Board.</p>

    @if (session('status'))
        <div class="alert mt-5" role="status">{{ session('status') }}</div>
    @endif

    <form wire:submit="login" class="mt-6 space-y-4" novalidate>
        <div>
            <label for="email" class="field-label">E-mail</label>
            <input wire:model="email" id="email" type="email" autocomplete="email" inputmode="email" required autofocus class="field-input">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                <label for="password" class="field-label">Mot de passe</label>
                <a href="{{ route('password.request') }}" class="link text-sm" wire:navigate>Mot de passe oublié ?</a>
            </div>
            <input wire:model="password" id="password" type="password" autocomplete="current-password" required class="field-input">
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-ink">
            <input wire:model="remember" type="checkbox" class="size-4 rounded border-line accent-violet">
            Rester connecté
        </label>

        <button type="submit" class="btn-primary w-full sm:w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">Se connecter</span>
            <span wire:loading wire:target="login">Connexion…</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        Pas encore de compte ? <a href="{{ route('register') }}" class="link" wire:navigate>Inscris-toi</a>
    </p>
</div>
