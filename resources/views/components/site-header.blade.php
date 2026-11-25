{{-- En-tête des pages publiques (accueil, pages légales) : logo, liens de compte, menu mobile. --}}
<header class="sticky top-0 z-40 border-b border-line/80 bg-white/85 backdrop-blur supports-[backdrop-filter]:bg-white/75">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
        <x-logo dark />

        <nav aria-label="Navigation principale" class="hidden items-center gap-1 text-sm md:flex">
            <a href="{{ route('home') }}#comment-ca-marche" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Comment ça marche</a>
            <a href="{{ route('home') }}#communaute" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">La communauté</a>
            <span aria-hidden="true" class="mx-2 h-5 w-px bg-line"></span>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary text-sm">Mon espace</a>
            @else
                <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 font-semibold text-gray-900 hover:bg-gray-50">Connexion</a>
                <a href="{{ route('register') }}" class="btn-primary ml-1 text-sm">Créer mon compte</a>
            @endauth
        </nav>

        <button type="button" data-menu-toggle="public-menu" aria-controls="public-menu" aria-expanded="false"
                class="-mr-2 grid size-10 place-items-center rounded-lg text-gray-900 hover:bg-gray-100 md:hidden">
            @svg('heroicon-o-bars-3', 'size-6', ['aria-hidden' => 'true'])
            <span class="sr-only">Ouvrir le menu</span>
        </button>
    </div>

    {{-- Menu mobile : panneau plein écran, fermé par défaut. --}}
    <div id="public-menu" data-menu hidden class="fixed inset-0 z-50 bg-white md:hidden">
        <div class="flex h-16 items-center justify-between border-b border-line px-4">
            <x-logo dark />
            <button type="button" data-menu-close class="-mr-2 grid size-10 place-items-center rounded-lg text-gray-900 hover:bg-gray-100">
                @svg('heroicon-o-x-mark', 'size-6', ['aria-hidden' => 'true'])
                <span class="sr-only">Fermer le menu</span>
            </button>
        </div>
        <nav aria-label="Navigation mobile" class="flex flex-col gap-1 px-4 py-6 text-lg">
            <a href="{{ route('home') }}#comment-ca-marche" data-menu-close class="rounded-lg px-3 py-3 font-medium text-gray-900 hover:bg-gray-50">Comment ça marche</a>
            <a href="{{ route('home') }}#communaute" data-menu-close class="rounded-lg px-3 py-3 font-medium text-gray-900 hover:bg-gray-50">La communauté</a>
            <div class="mt-6 flex flex-col gap-3 border-t border-line pt-6">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary py-3 text-base">Mon espace</a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary py-3 text-base">Créer mon compte</a>
                    <a href="{{ route('login') }}" class="btn-secondary py-3 text-base">Connexion</a>
                @endauth
            </div>
        </nav>
    </div>
</header>
