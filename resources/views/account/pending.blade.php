<x-layouts::app title="Compte en cours de validation">
    <div class="card mx-auto max-w-2xl text-center">
        <div class="mx-auto grid size-12 place-items-center rounded-full bg-lime/30 text-ink ring-4 ring-lime/15" aria-hidden="true">@svg('heroicon-o-clock', 'size-7', ['aria-hidden' => 'true'])</div>
        <h1 class="mt-4 text-2xl font-semibold tracking-tight text-ink">Ton compte est en cours de validation par l'équipe</h1>
        <p class="mt-3 text-gray-700">
            Merci {{ $user->first_name }} ! L'équipe Bénin Codeurs vérifie que tu fais partie de la communauté VIP.
            Tu recevras un e-mail à <strong>{{ $user->email }}</strong> dès que ton compte sera validé.
        </p>
        <p class="mt-3 text-sm text-gray-600">En attendant, tu peux préparer ton profil depuis ton espace.</p>
        <a href="{{ route('dashboard') }}" class="btn-secondary mt-6">Retour à mon espace</a>
    </div>
</x-layouts::app>
