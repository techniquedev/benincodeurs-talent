@php
    $supportWhatsappUrl = config('talent.support_whatsapp_url');
    $socialNetworks = [
        'linkedin' => ['LinkedIn', 'M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13ZM7.12 20.45H3.56V9h3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0Z'],
        'facebook' => ['Facebook', 'M24 12.07C24 5.41 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.5 0-1.96.93-1.96 1.89v2.26h3.32l-.53 3.49h-2.8V24C19.62 23.1 24 18.1 24 12.07Z'],
        'instagram' => ['Instagram', 'M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.15 3.23-1.66 4.77-4.92 4.92-1.27.06-1.64.07-4.85.07s-3.58-.01-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92-.06-1.27-.07-1.64-.07-4.85s.01-3.58.07-4.85C2.38 3.92 3.9 2.38 7.15 2.23 8.42 2.17 8.8 2.16 12 2.16ZM12 0C8.74 0 8.33.01 7.05.07 2.7.27.27 2.69.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.2 4.36 2.62 6.78 6.98 6.98 1.28.06 1.69.07 4.95.07s3.67-.01 4.95-.07c4.35-.2 6.78-2.62 6.98-6.98.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95C23.73 2.7 21.31.27 16.95.07 15.67.01 15.26 0 12 0Zm0 5.84a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.4-11.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88Z'],
        'youtube' => ['YouTube', 'M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.5A3.02 3.02 0 0 0 .5 6.19C0 8.07 0 12 0 12s0 3.93.5 5.81a3.02 3.02 0 0 0 2.12 2.14c1.88.5 9.38.5 9.38.5s7.5 0 9.38-.5a3.02 3.02 0 0 0 2.12-2.14C24 15.93 24 12 24 12s0-3.93-.5-5.81ZM9.55 15.57V8.43L15.82 12l-6.27 3.57Z'],
        'x' => ['X', 'M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.47l8.6-9.83L0 1.15h7.59l5.24 6.93 6.07-6.93Zm-1.29 19.5h2.04L6.48 3.24H4.3l13.31 17.41Z'],
    ];
    $activeSocialNetworks = collect($socialNetworks)->filter(fn ($network, $key) => filled(config("talent.social.$key")));
    $columnTitle = 'text-xs font-semibold tracking-wider text-white uppercase';
    $columnLink = 'text-sm text-white/70 transition hover:text-lime';
@endphp
<footer {{ $attributes->merge(['class' => 'bg-night text-white']) }}>
    <div class="mx-auto max-w-6xl px-4 pt-12 pb-8 sm:px-6 lg:pt-16">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1.2fr_1fr]">
            <div class="sm:col-span-2 lg:col-span-1">
                <x-logo />
                <p class="mt-4 max-w-xs text-sm text-white/70">
                    L'annuaire des talents de la communauté VIP Bénin Codeurs : se faire connaître, se trouver, travailler ensemble.
                </p>
                @if ($activeSocialNetworks->isNotEmpty())
                    <ul class="mt-5 flex items-center gap-2" aria-label="Réseaux sociaux de Bénin Codeurs">
                        @foreach ($activeSocialNetworks as $key => [$networkName, $iconPath])
                            <li>
                                <a href="{{ config("talent.social.$key") }}" target="_blank" rel="noopener noreferrer"
                                   class="grid size-10 place-items-center rounded-lg border border-white/15 text-white/70 transition hover:border-lime/60 hover:text-lime">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" class="size-4"><path d="{{ $iconPath }}"/></svg>
                                    <span class="sr-only">{{ $networkName }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <nav aria-labelledby="footer-talent">
                <h2 id="footer-talent" class="{{ $columnTitle }}">Talent Board</h2>
                <ul class="mt-4 space-y-3">
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="{{ $columnLink }}">Mon espace</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="{{ $columnLink }}">Mon profil</a></li>
                        @if (auth()->user()->isApproved() || auth()->user()->is_admin)
                            <li><a href="{{ route('directory') }}" class="{{ $columnLink }}">Annuaire</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('register') }}" class="{{ $columnLink }}">Créer mon compte</a></li>
                        <li><a href="{{ route('login') }}" class="{{ $columnLink }}">Connexion</a></li>
                        <li><a href="{{ route('home') }}#comment-ca-marche" class="{{ $columnLink }}">Comment ça marche</a></li>
                    @endauth
                </ul>
            </nav>

            <nav aria-labelledby="footer-community">
                <h2 id="footer-community" class="{{ $columnTitle }}">Bénin Codeurs</h2>
                <ul class="mt-4 space-y-3">
                    <li><a href="https://communaute.benincodeurs.com" target="_blank" rel="noopener" class="{{ $columnLink }}">Communauté VIP</a></li>
                    <li><a href="https://ccn.benincodeurs.com" target="_blank" rel="noopener" class="{{ $columnLink }}">Cotonou Code la Nuit</a></li>
                    <li><a href="https://bilan.benincodeurs.com" target="_blank" rel="noopener" class="{{ $columnLink }}">Bilan</a></li>
                </ul>
            </nav>

            <nav aria-labelledby="footer-help">
                <h2 id="footer-help" class="{{ $columnTitle }}">Aide</h2>
                <ul class="mt-4 space-y-3">
                    @if (filled($supportWhatsappUrl))
                        <li><a href="{{ $supportWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="{{ $columnLink }}">Nous écrire sur WhatsApp</a></li>
                    @endif
                    <li><a href="{{ route('legal.privacy') }}" class="{{ $columnLink }}">Confidentialité</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="{{ $columnLink }}">Conditions d'utilisation</a></li>
                </ul>
            </nav>
        </div>

        <div class="mt-12 flex flex-col gap-2 border-t border-white/10 pt-6 text-xs text-white/70 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} Bénin Codeurs · Talent Board de la communauté VIP</p>
            <p>Photos : Wezon Media</p>
        </div>
    </div>
</footer>
