@php
    $supportWhatsappUrl = config('talent.support_whatsapp_url');
    $supportWhatsappNumber = config('talent.support_whatsapp_number');
    $socialNetworks = \App\Support\SocialIcons::networks();
    $activeSocialNetworks = collect($socialNetworks)->filter(fn ($network, $key) => filled(config("talent.social.$key")));
    $columnTitle = 'text-xs font-semibold tracking-wider text-white uppercase';
    $columnLink = 'text-sm text-white/70 transition hover:text-lime';
@endphp
<footer {{ $attributes->merge(['class' => 'bg-night text-white']) }}>
    <div class="mx-auto max-w-6xl px-4 pt-12 pb-8 sm:px-6 lg:pt-16">
        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1.2fr)_minmax(0,1fr)]">
            <div class="sm:col-span-2 lg:col-span-1">
                <x-logo />
                <p class="mt-4 max-w-xs text-sm text-white/70">
                    Le Talent Board de la communauté VIP Bénin Codeurs : ton profil pour te faire connaître de l'équipe.
                </p>
                @if ($activeSocialNetworks->isNotEmpty())
                    <ul class="mt-5 flex flex-wrap items-center gap-2" aria-label="Réseaux sociaux de Bénin Codeurs">
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
                        @if (auth()->user()->can('admin.view'))
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

            <div aria-labelledby="footer-contact" role="group">
                <h2 id="footer-contact" class="{{ $columnTitle }}">Contact</h2>
                <ul class="mt-4 space-y-3">
                    @if (filled($supportWhatsappUrl))
                        <li>
                            <a href="{{ $supportWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="{{ $columnLink }} inline-flex items-center gap-2">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" class="size-4 shrink-0"><path d="{{ \App\Support\SocialIcons::WHATSAPP }}"/></svg>
                                <span class="min-w-0">WhatsApp{{ filled($supportWhatsappNumber) ? ' · '.$supportWhatsappNumber : '' }}</span>
                            </a>
                        </li>
                    @endif
                    <li class="text-sm text-white/70">Cotonou, Bénin</li>
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-3 border-t border-white/10 pt-6 text-xs text-white/70 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} Bénin Codeurs · Talent Board de la communauté VIP · Photos : Wezon Media</p>
            <nav aria-label="Informations légales" class="flex flex-wrap gap-x-5 gap-y-2">
                <a href="https://benincodeurs.com/mentions-legales" target="_blank" rel="noopener" class="transition hover:text-lime">Mentions légales</a>
                <a href="{{ route('legal.privacy') }}" class="transition hover:text-lime">Confidentialité</a>
                <a href="{{ route('legal.terms') }}" class="transition hover:text-lime">Conditions d'utilisation</a>
            </nav>
        </div>
    </div>
</footer>
