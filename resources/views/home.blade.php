@php
    $steps = [
        ['etape-1.jpg', 'Inscris-toi', "Ton e-mail d'adhésion VIP, le code reçu par e-mail, tes informations et ton mot de passe."],
        ['etape-2.jpg', 'Remplis ton profil', 'Six étapes courtes, enregistrées au fur et à mesure : métier, niveau, compétences, réalisations, liens.'],
        ['etape-3.jpg', 'Fais-toi connaître', "Une fois ton profil complet, l'équipe Bénin Codeurs peut le consulter pour te proposer des missions et des opportunités."],
    ];
    $features = [
        ['feature-1.jpg', 'identification', 'Te faire connaître', "Métier, niveau, compétences et réalisations au même endroit : l'équipe Bénin Codeurs sait ce que tu sais faire."],
        ['feature-2.jpg', 'puzzle-piece', 'Montrer ton travail', "Jusqu'à 10 compétences avec ton niveau, dont 3 mises en avant, et 3 réalisations avec image et lien."],
        ['feature-3.jpg', 'briefcase', 'Être contacté au bon moment', "Quand une mission ou une opportunité correspond à ton profil, l'équipe peut te la proposer. Rien n'est garanti : plus ton profil est précis, plus il est utile."],
        ['feature-4.jpg', 'lock-closed', "Vu par l'équipe seulement", "Ton profil n'est ni public ni visible par les autres membres : seule l'équipe Bénin Codeurs le consulte."],
    ];
    $faq = [
        ['Qui peut accéder au Talent Board ?', "Les membres de la communauté VIP Bénin Codeurs. À l'inscription, ton e-mail d'adhésion est vérifié automatiquement, puis tu confirmes ton adresse avec un code. L'inscription est gratuite."],
        ['Qui voit mon profil ?', "Uniquement l'équipe Bénin Codeurs. Ton profil, tes coordonnées et ton entreprise ne sont ni publics ni visibles par les autres membres. L'équipe s'en sert pour te proposer, quand ton profil correspond, des missions ou des opportunités."],
        ['Comment modifier mon profil ?', "Connecte-toi, puis ouvre « Mon profil » depuis ton espace. Chaque étape est enregistrée au fur et à mesure : tu peux revenir compléter ou corriger tes informations quand tu veux."],
        ['Comment supprimer mon compte ?', "Écris à l'équipe Bénin Codeurs sur WhatsApp. Ton compte, ton profil, tes réalisations et tes images sont alors supprimés définitivement."],
    ];
    $image = fn (string $file): string => asset('images/home/'.$file);
@endphp
<x-layouts::public :overlay-header="true" description="Le Talent Board de la communauté VIP Bénin Codeurs : ton profil, tes compétences et tes réalisations, pour te faire connaître de l'équipe.">
    {{-- Héros : sombre, la barre de navigation est posée dessus (transparente). --}}
    <section class="relative isolate overflow-hidden bg-linear-to-br from-night to-brand text-white">
        <div aria-hidden="true" class="glow pointer-events-none absolute inset-0 -z-10"></div>
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 pt-24 pb-14 sm:px-6 sm:pt-28 md:grid-cols-[1.05fr_1fr] md:gap-12 lg:gap-16 lg:pt-36 lg:pb-24">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold text-white/90">
                    <span aria-hidden="true" class="size-2 rounded-full bg-lime"></span>
                    Communauté VIP Bénin Codeurs
                </p>
                <h1 class="mt-5 text-4xl leading-[1.08] font-bold tracking-tight text-balance sm:text-5xl lg:text-6xl">
                    Fais connaître tes talents <span class="text-lime">à l'équipe Bénin Codeurs.</span>
                </h1>
                <p class="mt-5 max-w-xl text-base text-white/80 sm:text-lg">
                    Développeurs, designers, experts data et IA, chefs de projet, marketeurs : crée ton profil, montre tes compétences
                    et tes réalisations. L'équipe s'en sert pour proposer des missions et des opportunités aux membres de la communauté.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="btn-lime px-6 py-3 text-base">Créer mon profil @svg('heroicon-m-arrow-right', 'size-4', ['aria-hidden' => 'true'])</a>
                    <a href="{{ route('login') }}" class="btn-ghost-light px-6 py-3 text-base">J'ai déjà un compte</a>
                </div>
                <ul class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-white/80" aria-label="En bref">
                    @foreach (['Réservé aux membres VIP', 'Gratuit', 'Profils vérifiés'] as $trustPoint)
                        <li class="inline-flex items-center gap-1.5">
                            @svg('heroicon-m-check-circle', 'size-4 text-lime', ['aria-hidden' => 'true'])
                            {{ $trustPoint }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="relative mx-auto w-full max-w-md md:max-w-none">
                <div class="media-fallback relative aspect-[4/5] overflow-hidden rounded-3xl shadow-2xl ring-1 shadow-night/60 ring-white/15">
                    <img src="{{ $image('hero.jpg') }}" alt="Des membres de la communauté Bénin Codeurs au travail" width="1200" height="1500"
                         fetchpriority="high" decoding="async" data-fallback class="size-full object-cover">
                </div>
            </div>
        </div>
    </section>

    {{-- Comment ça marche : clair --}}
    <section id="comment-ca-marche" class="scroll-mt-16 bg-paper py-16 sm:py-24" aria-labelledby="how">
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
                            <span class="absolute top-3 left-3 grid size-9 place-items-center rounded-full bg-lime text-sm font-bold text-night shadow-md">{{ $i + 1 }}</span>
                        </div>
                        <div class="p-5 sm:p-6">
                            <h3 class="text-lg font-semibold text-ink">{{ $stepTitle }}</h3>
                            <p class="mt-1.5 text-sm text-muted">{{ $stepText }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Pensé pour la communauté : sombre --}}
    <section id="communaute" class="scroll-mt-16 bg-brand text-white" aria-labelledby="why">
        {{--
            Mobile et tablette : la photo reste collée en haut (environ 40 % de l'écran) et les 4 cartes défilent dessous.
            La carte au centre de l'écran choisit la photo affichée (fondu) et le point actif (IntersectionObserver, app.js).
        --}}
        <div data-story-mobile class="pt-16 pb-16 sm:pt-24 lg:hidden">
            <div class="mx-auto max-w-2xl px-4 sm:px-6">
                <p class="eyebrow-dark">Pourquoi le Talent Board</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Pensé pour la communauté</h2>
            </div>

            <div class="sticky top-16 z-10 mt-8 bg-brand px-4 pt-3 pb-4 sm:px-6">
                <div class="media-fallback relative mx-auto h-[40svh] max-w-2xl overflow-hidden rounded-card ring-1 ring-white/15">
                    @foreach ($features as $i => [$featureImage, $icon, $featureTitle])
                        <img data-story-photo src="{{ $image($featureImage) }}" alt="{{ $featureTitle }}" width="1200" height="900" loading="lazy" decoding="async" data-fallback
                             @class(['story-photo absolute inset-0 size-full object-cover', 'is-active' => $i === 0])>
                    @endforeach
                </div>
                <ol class="mt-3 flex justify-center gap-2" aria-hidden="true">
                    @foreach ($features as $i => $feature)
                        <li data-story-dot @class(['story-dot h-1.5 rounded-full bg-white/30', 'is-active' => $i === 0])></li>
                    @endforeach
                </ol>
            </div>

            <ol class="mx-auto mt-4 max-w-2xl space-y-4 px-4 pb-[20svh] sm:px-6">
                @foreach ($features as $i => [$featureImage, $icon, $featureTitle, $featureText])
                    <li data-story-card class="rounded-card border border-white/10 bg-night/50 p-5">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 place-items-center rounded-lg bg-white/10 text-lime" aria-hidden="true">@svg('heroicon-o-'.$icon, 'size-5')</span>
                            <span class="text-xs font-semibold tracking-wider text-white/70 uppercase">{{ sprintf('%02d', $i + 1) }} / {{ sprintf('%02d', count($features)) }}</span>
                        </div>
                        <h3 class="mt-3 text-lg font-semibold">{{ $featureTitle }}</h3>
                        <p class="mt-1 text-white/75">{{ $featureText }}</p>
                    </li>
                @endforeach
            </ol>
        </div>

        {{--
            Ordinateur : défilement narratif. Le bloc collant (titre, points, photo) fait un écran et il est visible
            dès l'entrée dans la section ; la zone fait 4 écrans, soit environ un écran de défilement par point.
            Le point actif suit la progression du défilement (app.js) ; le 1er est actif d'emblée.
        --}}
        <div data-story class="relative hidden lg:block lg:h-[calc(400dvh-16rem)]">
            <div class="sticky top-16 flex h-[calc(100dvh-4rem)] items-center overflow-hidden">
                <div class="mx-auto grid w-full max-w-6xl grid-cols-[1fr_1.1fr] items-center gap-16 px-6 py-10">
                    <div>
                        <p class="eyebrow-dark">Pourquoi le Talent Board</p>
                        <h2 id="why" class="mt-2 text-4xl font-bold tracking-tight">Pensé pour la communauté</h2>
                        <ol class="mt-8 space-y-1">
                            @foreach ($features as $i => [$featureImage, $icon, $featureTitle, $featureText])
                                <li data-story-step @class(['story-step border-l-2 border-white/15 py-3 pr-4 pl-6', 'is-active' => $i === 0]) @if ($i === 0) aria-current="step" @endif>
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 place-items-center rounded-lg bg-white/10 text-lime" aria-hidden="true">@svg('heroicon-o-'.$icon, 'size-5')</span>
                                        <h3 class="text-lg font-semibold">{{ $featureTitle }}</h3>
                                    </div>
                                    <p class="mt-1.5 text-white/75">{{ $featureText }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="media-fallback relative aspect-[4/3] overflow-hidden rounded-3xl ring-1 ring-white/15">
                        @foreach ($features as $i => [$featureImage, $icon, $featureTitle])
                            <img data-story-photo src="{{ $image($featureImage) }}" alt="{{ $featureTitle }}" width="1200" height="900" decoding="async" data-fallback
                                 @class(['story-photo absolute inset-0 size-full object-cover', 'is-active' => $i === 0])>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Questions fréquentes : clair, accordéon natif (fonctionne sans JavaScript). --}}
    <section id="faq" class="scroll-mt-16 bg-paper pt-16 sm:pt-24" aria-labelledby="faq-title">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <p class="eyebrow">Questions fréquentes</p>
            <h2 id="faq-title" class="mt-2 text-3xl font-bold tracking-tight text-ink sm:text-4xl">Tu te demandes peut-être…</h2>
            <div class="mt-8 space-y-3">
                @foreach ($faq as $i => [$question, $answer])
                    <details name="faq" class="faq-item group rounded-card border border-line bg-white" @if ($i === 0) open @endif>
                        <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-semibold text-ink [&::-webkit-details-marker]:hidden">
                            {{ $question }}
                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-violet/10 text-violet transition group-open:rotate-45" aria-hidden="true">
                                @svg('heroicon-m-plus', 'size-4')
                            </span>
                        </summary>
                        <p class="px-5 pb-5 text-muted">{{ $answer }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Appel final : photo sous un voile violet nuit (night → brand, ~85 %), sur fond clair --}}
    <section class="bg-paper px-4 py-16 sm:px-6 sm:py-24">
        <div class="relative isolate mx-auto max-w-6xl overflow-hidden rounded-3xl bg-night px-6 py-16 text-center text-white sm:px-12 sm:py-24">
            <img src="{{ $image('cta.jpg') }}" alt="" width="1200" height="514" loading="lazy" decoding="async" data-fallback
                 class="absolute inset-0 -z-20 size-full object-cover">
            <div aria-hidden="true" class="absolute inset-0 -z-10 bg-linear-to-br from-night/85 to-brand/85"></div>
            <h2 class="text-3xl font-bold tracking-tight text-balance sm:text-4xl">Prêt à te faire connaître ?</h2>
            <p class="mx-auto mt-3 max-w-xl text-white/85">Crée ton compte en une minute, puis complète ton profil à ton rythme.</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="btn-lime px-6 py-3">Créer mon compte</a>
                <a href="{{ route('login') }}" class="btn-ghost-light px-6 py-3">Se connecter</a>
            </div>
        </div>
    </section>
</x-layouts::public>
