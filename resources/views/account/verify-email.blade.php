<x-layouts::guest title="Confirme ton e-mail">
    <div class="card">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Confirme ton adresse e-mail</h1>
        <p class="mt-3 text-sm text-ink">
            Nous venons d'envoyer un lien de confirmation à <strong>{{ auth()->user()->email }}</strong>.
            Clique dessus pour activer ton compte. Pense à vérifier tes courriers indésirables.
        </p>

        @if (session('status'))
            <div class="alert mt-5" role="status">{{ session('status') }}</div>
        @endif

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-primary">Renvoyer le lien</button>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="link text-sm">Se déconnecter</button>
            </form>
        </div>
    </div>
</x-layouts::guest>
