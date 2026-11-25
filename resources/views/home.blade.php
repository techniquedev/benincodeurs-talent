<x-layouts::guest title="Bienvenue">
    <div class="text-white">
        <p class="inline-flex rounded-full bg-lime px-3 py-1 text-xs font-bold uppercase tracking-wide text-ink">Communauté VIP</p>
        <h1 class="mt-4 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">
            Les talents de <span class="text-lime">Bénin Codeurs</span>, réunis au même endroit.
        </h1>
        <p class="mt-4 text-base text-white/80">
            Crée ton profil (métier, niveau, compétences, réalisations) et retrouve les autres membres de la communauté dans l'annuaire.
        </p>
        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="btn-primary">Créer mon compte</a>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-white/30 px-6 py-3 font-semibold text-white hover:bg-white/10">Se connecter</a>
        </div>
        <p class="mt-6 text-xs text-white/60">L'annuaire est réservé aux membres dont le compte a été validé par l'équipe.</p>
    </div>
</x-layouts::guest>
