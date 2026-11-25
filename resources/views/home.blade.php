@php
    $steps = [
        ['etape-1.jpg', 'Inscris-toi', "Ton e-mail d'adhésion VIP, le code reçu par e-mail, tes informations et ton mot de passe."],
        ['etape-2.jpg', 'Remplis ton profil', 'Six étapes courtes, enregistrées au fur et à mesure : métier, niveau, compétences, réalisations, liens.'],
        ['etape-3.jpg', "Rejoins l'annuaire", "Dès que ton profil est complet, tu apparais dans l'annuaire des membres."],
    ];
    $features = [
        ['feature-1.jpg', 'magnifying-glass', 'Trouver vite', 'Recherche et filtres par métier, compétence, niveau, ville et disponibilité : la bonne personne en quelques secondes.'],
        ['feature-2.jpg', 'puzzle-piece', 'Montrer son travail', "Jusqu'à 10 compétences avec ton niveau, et 3 réalisations avec image et lien."],
        ['feature-3.jpg', 'users', 'Collaborer', 'Missions, emploi, projets communs : chacun indique sa disponibilité.'],
        ['feature-4.jpg', 'lock-closed', 'Entre membres', "L'annuaire est réservé aux membres validés. Tu choisis d'afficher ou non ton WhatsApp, ton e-mail et ton entreprise."],
    ];
    $statItems = [
        [$stats['members'], $stats['members'] > 1 ? 'membres validés' : 'membre validé'],
        [$stats['professions'], $stats['professions'] > 1 ? 'métiers représentés' : 'métier représenté'],
        [$stats['cities'], $stats['cities'] > 1 ? 'villes' : 'ville'],
    ];
    $image = fn (string $file): string => asset('images/home/'.$file);
@endphp
<x-layouts::public description="Le Talent Board réunit les membres de la communauté VIP Bénin Codeurs : profils, compétences, réalisations et annuaire.">
    {{-- Héros --}}
    <section class="relative overflow-hidden">
        <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[32rem] bg-linear-to-b from-paper to-white"></div>
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 pt-10 pb-14 sm:px-6 sm:pt-14 md:grid-cols-[1.05fr_1fr] md:gap-12 lg:gap-16 lg:pt-20 lg:pb-24">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-3 py-1 text-xs font-semibold text-gray-700">
                    <span aria-hidden="true" class="size-2 rounded-full bg-lime ring-2 ring-lime/30"></span>
                    Communauté VIP Bénin Codeurs
                </p>
                <h1 class="mt-5 text-4xl leading-[1.08] font-bold tracking-tight text-ink sm:text-5xl lg:text-6xl">
                    Les talents de la communauté, <span class="text-violet">réunis au même endroit.</span>
                </h1>
                <p class="mt-5 max-w-xl text-base text-gray-600 sm:text-lg">
                    Développeurs, designers, experts data et IA, chefs de projet, marketeurs : crée ton profil, montre tes réalisations
                    et trouve les bonnes personnes pour collaborer, recruter ou te lancer en mission.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="btn-primary px-6 py-3 text-base">Créer mon profil @svg('heroicon-m-arrow-right', 'size-4', ['aria-hidden' => 'true'])</a>
                    <a href="{{ route('login') }}" class="btn-secondary px-6 py-3 text-base">J'ai déjà un compte</a>
                </div>
                <p class="mt-5 text-xs text-gray-500">Accès réservé aux membres VIP : ton adhésion est vérifiée dès l'inscription.</p>

                @if ($stats['members'] > 0)
                    <dl class="mt-10 grid max-w-lg grid-cols-3 divide-x divide-line border-y border-line">
                        @foreach ($statItems as [$statValue, $statLabel])
                            <div class="flex flex-col-reverse gap-1 py-4 pl-4 first:pl-0">
                                <dt class="text-xs text-gray-500 sm:text-sm">{{ $statLabel }}</dt>
                                <dd class="text-2xl font-bold tracking-tight text-ink tabular-nums sm:text-3xl">{{ number_format($statValue, 0, ',', ' ') }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>

            <div class="relative mx-auto w-full max-w-md md:max-w-none">
                <div aria-hidden="true" class="absolute -right-3 -bottom-3 hidden size-28 rounded-2xl bg-lime md:block"></div>
                <div class="media-fallback relative aspect-[4/5] overflow-hidden rounded-3xl ring-1 ring-black/5">
                    <img src="{{ $image('hero.jpg') }}" alt="Des membres de la communauté Bénin Codeurs au travail" width="1200" height="1500"
                         fetchpriority="high" decoding="async" data-fallback class="size-full object-cover">
                </div>
            </div>
        </div>
    </section>

    {{-- Comment ça marche --}}
    <section id="comment-ca-marche" class="scroll-mt-16 border-t border-line bg-paper py-16 sm:py-24" aria-labelledby="how">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="max-w-2xl">
                <p class="eyebrow">En trois étapes</p>
                <h2 id="how" class="mt-2 text-3xl font-bold tracking-tight text-ink sm:text-4xl">Comment ça marche</h2>
            </div>
            <ol class="mt-10 grid gap-5 md:grid-cols-3">
                @foreach ($steps as $i => [$stepImage, $stepTitle, $stepText])
                    <li class="flex flex-col overflow-hidden rounded-card border border-line bg-white">
                        <div class="media-fallback relative aspect-[2/1]">
                            <img src="{{ $image($stepImage) }}" alt="" width="1200" height="600" loading="lazy" decoding="async" data-fallback
                                 class="absolute inset-0 size-full object-cover">
                            <span class="absolute top-3 left-3 grid size-9 place-items-center rounded-full bg-lime text-sm font-bold text-ink shadow-md ring-4 ring-white/70">{{ $i + 1 }}</span>
                        </div>
                        <div class="p-5 sm:p-6">
                            <h3 class="text-lg font-semibold text-ink">{{ $stepTitle }}</h3>
                            <p class="mt-1.5 text-sm text-gray-600">{{ $stepText }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Pensé pour la communauté --}}
    <section id="communaute" class="scroll-mt-16 py-16 sm:py-24" aria-labelledby="why">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="max-w-2xl">
                <p class="eyebrow">Pourquoi le Talent Board</p>
                <h2 id="why" class="mt-2 text-3xl font-bold tracking-tight text-ink sm:text-4xl">Pensé pour la communauté</h2>
            </div>

            {{-- Mobile et tablette : pile de cartes avec photo. --}}
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:hidden">
                @foreach ($features as [$featureImage, $icon, $featureTitle, $featureText])
                    <article class="overflow-hidden rounded-card border border-line bg-white">
                        <div class="media-fallback aspect-[4/3]">
                            <img src="{{ $image($featureImage) }}" alt="" width="1200" height="900" loading="lazy" decoding="async" data-fallback class="size-full object-cover">
                        </div>
                        <div class="p-5">
                            <span class="grid size-9 place-items-center rounded-lg bg-violet/10 text-violet" aria-hidden="true">@svg('heroicon-o-'.$icon, 'size-5')</span>
                            <h3 class="mt-3 font-semibold text-ink">{{ $featureTitle }}</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $featureText }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        {{-- Ordinateur : défilement narratif. La zone fait 4 hauteurs d'écran, le contenu reste collé en haut. --}}
        <div data-story class="relative hidden lg:block lg:h-[320vh]">
            <div class="sticky top-16 flex h-[calc(100dvh-4rem)] items-center">
                <div class="mx-auto grid w-full max-w-6xl grid-cols-[1fr_1.15fr] items-center gap-16 px-6">
                    <ol class="space-y-2">
                        @foreach ($features as $i => [$featureImage, $icon, $featureTitle, $featureText])
                            <li data-story-step @class(['story-step border-l-2 border-line py-4 pr-4 pl-6', 'is-active' => $i === 0])>
                                <div class="flex items-center gap-3">
                                    <span class="grid size-9 place-items-center rounded-lg bg-violet/10 text-violet" aria-hidden="true">@svg('heroicon-o-'.$icon, 'size-5')</span>
                                    <h3 class="text-lg font-semibold text-ink">{{ $featureTitle }}</h3>
                                </div>
                                <p class="mt-2 text-gray-600">{{ $featureText }}</p>
                            </li>
                        @endforeach
                    </ol>

                    <div class="media-fallback relative aspect-[4/3] overflow-hidden rounded-3xl ring-1 ring-black/5">
                        @foreach ($features as $i => [$featureImage, $icon, $featureTitle])
                            <img data-story-photo src="{{ $image($featureImage) }}" alt="{{ $featureTitle }}" width="1200" height="900" loading="lazy" decoding="async" data-fallback
                                 @class(['story-photo absolute inset-0 size-full object-cover', 'is-active' => $i === 0])>
                        @endforeach
                        <span aria-hidden="true" class="absolute right-4 bottom-4 h-1.5 w-16 rounded-full bg-lime"></span>
                    </div>
                </div>
            </div>

            {{-- Repères invisibles : chacun active une étape quand il passe au milieu de l'écran. --}}
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 flex flex-col">
                @foreach ($features as $feature)
                    <div data-story-trigger class="flex-1"></div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Appel final --}}
    <section class="px-4 pb-16 sm:px-6 sm:pb-24">
        <div class="relative isolate mx-auto max-w-6xl overflow-hidden rounded-3xl bg-night px-6 py-16 text-center text-white sm:px-12 sm:py-24">
            <img src="{{ $image('cta.jpg') }}" alt="" width="1200" height="514" loading="lazy" decoding="async" data-fallback
                 class="absolute inset-0 -z-20 size-full object-cover">
            <div aria-hidden="true" class="absolute inset-0 -z-10 bg-linear-to-t from-night/95 via-night/80 to-night/60"></div>
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Prêt à rejoindre l'annuaire ?</h2>
            <p class="mx-auto mt-3 max-w-xl text-white/80">Crée ton compte en une minute, puis complète ton profil à ton rythme.</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-lime px-6 py-3 font-semibold text-ink transition hover:bg-lime-hover focus:outline-none focus-visible:ring-4 focus-visible:ring-lime/40">Créer mon compte</a>
                <a href="{{ route('login') }}" class="btn-ghost-light px-6 py-3">Se connecter</a>
            </div>
        </div>
    </section>
</x-layouts::public>
