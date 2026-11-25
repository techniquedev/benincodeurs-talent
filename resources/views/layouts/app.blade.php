<!DOCTYPE html>
<html lang="fr">
<head>
    @include('layouts.partials.head')
</head>
@php
    $currentUser = auth()->user();
    $canBrowse = $currentUser->isApproved() || $currentUser->is_admin;
    $navItems = array_filter([
        ['route' => 'dashboard', 'label' => 'Mon espace', 'icon' => 'home', 'active' => request()->routeIs('dashboard', 'account.pending')],
        ['route' => 'profile.edit', 'label' => 'Mon profil', 'icon' => 'user-circle', 'active' => request()->routeIs('profile.*')],
        $canBrowse ? ['route' => 'directory', 'label' => 'Annuaire', 'icon' => 'users', 'active' => request()->routeIs('directory', 'directory.*')] : null,
    ]);
@endphp
<body class="flex min-h-dvh flex-col bg-paper font-sans text-ink antialiased">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-lg focus:bg-violet focus:px-4 focus:py-2 focus:font-semibold focus:text-white">Aller au contenu</a>

    <header class="sticky top-0 z-40 border-b border-white/10 bg-night text-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
            <x-logo />

            <nav aria-label="Navigation principale" class="hidden h-full items-center gap-1 text-sm md:flex">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" @if ($item['active']) aria-current="page" @endif
                       @class([
                           'relative flex h-full items-center px-3 font-medium transition',
                           'text-white after:absolute after:inset-x-3 after:-bottom-px after:h-0.5 after:rounded-full after:bg-lime' => $item['active'],
                           'text-white/75 hover:text-white' => ! $item['active'],
                       ])>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                @if ($currentUser->is_admin)
                    <a href="{{ url('/admin') }}" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-white/75 hover:bg-white/10 hover:text-white">
                        <span aria-hidden="true" class="size-1.5 rounded-full bg-lime ring-2 ring-lime/30"></span> Admin
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost-light px-3 py-2 text-sm">Déconnexion</button>
                </form>
            </div>

            <button type="button" data-menu-toggle="app-menu" aria-controls="app-menu" aria-expanded="false"
                    class="-mr-2 grid size-10 place-items-center rounded-lg text-white hover:bg-white/10 md:hidden">
                @svg('heroicon-o-bars-3', 'size-6', ['aria-hidden' => 'true'])
                <span class="sr-only">Ouvrir le menu</span>
            </button>
        </div>

        {{-- Menu mobile : panneau plein écran. --}}
        <div id="app-menu" data-menu hidden class="glow fixed inset-0 z-50 flex flex-col bg-night md:hidden">
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-4">
                <x-logo />
                <button type="button" data-menu-close class="-mr-2 grid size-10 place-items-center rounded-lg text-white hover:bg-white/10">
                    @svg('heroicon-o-x-mark', 'size-6', ['aria-hidden' => 'true'])
                    <span class="sr-only">Fermer le menu</span>
                </button>
            </div>
            <div class="flex flex-1 flex-col overflow-y-auto px-4 py-6">
                <p class="px-3 text-sm text-white/70">Connecté en tant que <span class="font-semibold text-white">{{ $currentUser->first_name }}</span></p>
                <nav aria-label="Navigation mobile" class="mt-4 flex flex-col gap-1">
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}" @if ($item['active']) aria-current="page" @endif
                           @class([
                               'flex items-center gap-3 rounded-lg px-3 py-3 text-base font-medium',
                               'bg-white/10 text-white' => $item['active'],
                               'text-white/80 hover:bg-white/10 hover:text-white' => ! $item['active'],
                           ])>
                            @svg('heroicon-o-'.$item['icon'], 'size-5', ['aria-hidden' => 'true'])
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    @if ($currentUser->is_admin)
                        <a href="{{ url('/admin') }}" class="flex items-center gap-3 rounded-lg px-3 py-3 text-base font-medium text-white/80 hover:bg-white/10 hover:text-white">
                            @svg('heroicon-o-cog-6-tooth', 'size-5', ['aria-hidden' => 'true'])
                            Admin
                        </a>
                    @endif
                </nav>
                <form method="POST" action="{{ route('logout') }}" class="mt-auto border-t border-white/10 pt-6">
                    @csrf
                    <button type="submit" class="btn-ghost-light w-full py-3 text-base">Déconnexion</button>
                </form>
            </div>
        </div>
    </header>

    {{-- Chaque page commence par <x-page-header> (bande violet profond), puis son contenu dans .page-container. --}}
    <main id="contenu" class="flex-1">
        {{ $slot }}
    </main>

    <x-site-footer />
</body>
</html>
