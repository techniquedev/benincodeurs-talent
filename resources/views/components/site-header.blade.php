@props(['overlay' => false])
{{--
    En-tête des pages publiques (accueil, pages légales) : toujours sur fond sombre, logo clair.
    « overlay » (accueil) : posée sur le hero, transparente tant qu'on n'a pas défilé, puis pleine (voir app.css / app.js).
--}}
<header data-site-header @if ($overlay) data-overlay @endif
        @class(['site-header inset-x-0 top-0 z-40 text-white', 'fixed' => $overlay, 'sticky' => ! $overlay, 'is-scrolled' => ! $overlay])>
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
        <x-logo />

        <nav aria-label="Navigation principale" class="hidden items-center gap-1 text-sm md:flex">
            <a href="{{ route('home') }}#comment-ca-marche" class="rounded-lg px-3 py-2 font-medium text-white/80 transition hover:text-white">Comment ça marche</a>
            <a href="{{ route('home') }}#communaute" class="rounded-lg px-3 py-2 font-medium text-white/80 transition hover:text-white">La communauté</a>
            <span aria-hidden="true" class="mx-2 h-5 w-px bg-white/20"></span>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-lime px-4 py-2 text-sm">Mon espace</a>
            @else
                <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 font-semibold text-white transition hover:bg-white/10">Connexion</a>
                <a href="{{ route('register') }}" class="btn-lime ml-1 px-4 py-2 text-sm">Créer mon compte</a>
            @endauth
        </nav>

        <button type="button" data-menu-toggle="public-menu" aria-controls="public-menu" aria-expanded="false"
                class="-mr-2 grid size-10 place-items-center rounded-lg text-white hover:bg-white/10 md:hidden">
            @svg('heroicon-o-bars-3', 'size-6', ['aria-hidden' => 'true'])
            <span class="sr-only">Ouvrir le menu</span>
        </button>
    </div>

    {{-- Menu mobile : panneau plein écran, fermé par défaut. --}}
    <div id="public-menu" data-menu hidden class="glow fixed inset-0 z-50 overflow-y-auto bg-night md:hidden">
        <div class="flex h-16 items-center justify-between border-b border-white/10 px-4">
            <x-logo />
            <button type="button" data-menu-close class="-mr-2 grid size-10 place-items-center rounded-lg text-white hover:bg-white/10">
                @svg('heroicon-o-x-mark', 'size-6', ['aria-hidden' => 'true'])
                <span class="sr-only">Fermer le menu</span>
            </button>
        </div>
        <nav aria-label="Navigation mobile" class="flex flex-col gap-1 px-4 py-6 text-lg">
            <a href="{{ route('home') }}#comment-ca-marche" data-menu-close class="rounded-lg px-3 py-3 font-medium text-white hover:bg-white/10">Comment ça marche</a>
            <a href="{{ route('home') }}#communaute" data-menu-close class="rounded-lg px-3 py-3 font-medium text-white hover:bg-white/10">La communauté</a>
            <div class="mt-6 flex flex-col gap-3 border-t border-white/10 pt-6">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-lime py-3 text-base">Mon espace</a>
                @else
                    <a href="{{ route('register') }}" class="btn-lime py-3 text-base">Créer mon compte</a>
                    <a href="{{ route('login') }}" class="btn-ghost-light py-3 text-base">Connexion</a>
                @endauth
            </div>
        </nav>
    </div>
</header>
