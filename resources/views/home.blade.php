<!DOCTYPE html>
<html lang="fr">
<head>
    @include('layouts.partials.head', ['title' => null])
    <meta name="description" content="Le Talent Board réunit les membres de la communauté VIP Bénin Codeurs : profils, compétences, réalisations et annuaire.">
</head>
<body class="min-h-dvh bg-paper font-sans text-gray-900 antialiased">
    {{-- En-tête + héros --}}
    <div class="relative isolate overflow-hidden bg-night text-white">
        <div aria-hidden="true" class="pointer-events-none absolute -top-40 -right-32 -z-10 size-[28rem] rounded-full bg-violet/50 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-48 -left-32 -z-10 size-96 rounded-full bg-lime/15 blur-3xl"></div>

        <header class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-5 sm:px-6">
            <x-logo />
            <nav class="flex items-center gap-2 text-sm" aria-label="Compte">
                <a href="{{ route('login') }}" class="rounded-full px-4 py-2 font-semibold text-white/90 hover:bg-white/10">Connexion</a>
                <a href="{{ route('register') }}" class="hidden rounded-full bg-lime px-4 py-2 font-semibold text-ink hover:bg-lime-hover sm:inline-flex">Créer mon compte</a>
            </nav>
        </header>

        <section class="mx-auto grid max-w-6xl items-center gap-12 px-4 pt-8 pb-16 sm:px-6 sm:pt-12 sm:pb-24 lg:grid-cols-[1.1fr_1fr]">
            <div>
                <p class="inline-flex rounded-full bg-lime px-3 py-1 text-xs font-bold uppercase tracking-wide text-ink">Communauté VIP Bénin Codeurs</p>
                <h1 class="mt-5 text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl">
                    Les talents de la communauté, <span class="text-lime">réunis au même endroit.</span>
                </h1>
                <p class="mt-5 max-w-xl text-base text-white/80 sm:text-lg">
                    Développeurs, designers, experts data et IA, chefs de projet, marketeurs : crée ton profil, montre tes réalisations
                    et trouve les bonnes personnes pour collaborer, recruter ou te lancer en mission.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="btn-primary text-base">Créer mon profil</a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-white/30 px-6 py-3 font-semibold text-white hover:bg-white/10">J'ai déjà un compte</a>
                </div>
                <p class="mt-5 text-xs text-white/60">Accès réservé aux membres VIP. Chaque compte est validé par l'équipe Bénin Codeurs.</p>
            </div>

            {{-- Illustration : aperçu de cartes de l'annuaire --}}
            <div aria-hidden="true" class="relative mx-auto w-full max-w-md lg:max-w-none">
                <div class="absolute top-8 -right-2 hidden w-64 rotate-6 rounded-card bg-white/10 p-5 ring-1 ring-white/15 backdrop-blur sm:block">
                    <div class="flex items-center gap-3">
                        <span class="grid size-12 place-items-center rounded-full bg-lime font-extrabold text-ink">MA</span>
                        <div><p class="font-bold">Mariam A.</p><p class="text-xs text-white/70">Data analyst · Power BI</p></div>
                    </div>
                    <div class="mt-4 flex gap-1.5"><span class="badge bg-white/15 text-white">Senior</span><span class="badge bg-white/15 text-white">Ouvert à un emploi</span></div>
                </div>
                <div class="relative w-full max-w-sm -rotate-2 rounded-card bg-white p-6 text-gray-900 shadow-2xl shadow-black/40">
                    <span class="badge absolute top-5 right-5 bg-brand text-lime">★ À la une</span>
                    <div class="flex items-center gap-4">
                        <span class="grid size-16 place-items-center rounded-full bg-linear-to-br from-violet to-brand text-xl font-extrabold text-lime">KD</span>
                        <div>
                            <p class="text-lg font-extrabold text-brand">Koffi D.</p>
                            <p class="text-sm text-gray-600">Développeur Laravel et Vue.js</p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        <span class="badge bg-violet/10 text-violet">Confirmé</span>
                        <span class="badge bg-lime text-ink">Ouvert aux missions</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach (['Laravel', 'Vue.js', 'PostgreSQL'] as $demoSkill)
                            <span class="rounded-lg bg-paper px-2.5 py-1 text-xs font-semibold text-brand ring-1 ring-line">{{ $demoSkill }}</span>
                        @endforeach
                    </div>
                    <div class="mt-5 flex items-center justify-between text-sm text-gray-500">
                        <span>📍 Cotonou</span><span class="font-semibold text-violet">Voir le profil →</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <main>
        {{-- Comment ça marche --}}
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20" aria-labelledby="how">
            <h2 id="how" class="text-center text-2xl font-extrabold tracking-tight text-brand sm:text-3xl">Comment ça marche</h2>
            <ol class="mt-10 grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['Inscris-toi', 'Ton nom, ton e-mail et ton numéro WhatsApp. Confirme ton adresse e-mail.'],
                    ['Remplis ton profil', 'Six étapes courtes, enregistrées au fur et à mesure : métier, niveau, compétences, réalisations, liens.'],
                    ["Rejoins l'annuaire", "Une fois ton compte validé par l'équipe et ton profil complet, tu apparais dans l'annuaire des membres."],
                ] as $i => [$stepTitle, $stepText])
                    <li class="card relative">
                        <span class="grid size-10 place-items-center rounded-full bg-lime text-lg font-extrabold text-ink">{{ $i + 1 }}</span>
                        <h3 class="mt-4 text-lg font-bold text-brand">{{ $stepTitle }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $stepText }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Avantages --}}
        <section class="bg-white py-16 sm:py-20" aria-labelledby="why">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <h2 id="why" class="text-center text-2xl font-extrabold tracking-tight text-brand sm:text-3xl">Pensé pour la communauté</h2>
                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['🔎', 'Trouver vite', 'Recherche et filtres par métier, compétence, niveau, ville et disponibilité.'],
                        ['🧩', 'Montrer son travail', "Jusqu'à 10 compétences avec ton niveau, et 3 réalisations avec image et lien."],
                        ['🤝', 'Collaborer', 'Missions, emploi, projets communs : chacun indique sa disponibilité.'],
                        ['🔒', 'Entre membres', "L'annuaire est réservé aux membres validés. Tu choisis d'afficher ou non ton WhatsApp, ton e-mail et ton entreprise."],
                    ] as [$icon, $featureTitle, $featureText])
                        <div class="rounded-card bg-paper p-6 ring-1 ring-line">
                            <span class="text-2xl" aria-hidden="true">{{ $icon }}</span>
                            <h3 class="mt-3 font-bold text-brand">{{ $featureTitle }}</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $featureText }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Appel final --}}
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">
            <div class="relative overflow-hidden rounded-card bg-brand px-6 py-10 text-center text-white sm:px-12 sm:py-14">
                <div aria-hidden="true" class="pointer-events-none absolute -top-16 -left-10 size-56 rounded-full bg-violet/60 blur-3xl"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -right-10 -bottom-16 size-56 rounded-full bg-lime/20 blur-3xl"></div>
                <h2 class="relative text-2xl font-extrabold tracking-tight sm:text-3xl">Prêt à rejoindre l'annuaire ?</h2>
                <p class="relative mx-auto mt-2 max-w-xl text-white/80">Crée ton compte en une minute, puis complète ton profil à ton rythme.</p>
                <div class="relative mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="btn-primary">Créer mon compte</a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-white/30 px-6 py-3 font-semibold text-white hover:bg-white/10">Se connecter</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-line py-8 text-center text-xs text-gray-500">
        © {{ now()->year }} Bénin Codeurs · Talent Board de la communauté VIP
    </footer>
</body>
</html>
