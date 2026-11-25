<!DOCTYPE html>
<html lang="fr">
<head>
    @include('layouts.partials.head')
</head>
@php
    $currentUser = auth()->user();
    $canBrowse = $currentUser->isApproved() || $currentUser->is_admin;
    $navItems = array_filter([
        ['route' => 'dashboard', 'label' => 'Mon espace', 'active' => request()->routeIs('dashboard', 'account.pending')],
        ['route' => 'profile.edit', 'label' => 'Mon profil', 'active' => request()->routeIs('profile.*')],
        $canBrowse ? ['route' => 'directory', 'label' => 'Annuaire', 'active' => request()->routeIs('directory', 'directory.*')] : null,
    ]);
@endphp
<body class="flex min-h-dvh flex-col bg-paper font-sans text-gray-900 antialiased">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-full focus:bg-lime focus:px-4 focus:py-2 focus:font-semibold focus:text-ink">Aller au contenu</a>

    <header class="bg-brand">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6 sm:py-4">
            <x-logo />

            <nav aria-label="Navigation principale" class="hidden items-center gap-1 text-sm md:flex">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" @if ($item['active']) aria-current="page" @endif
                       @class([
                           'rounded-full px-4 py-2 font-semibold transition',
                           'bg-white/15 text-white' => $item['active'],
                           'text-white/80 hover:bg-white/10 hover:text-white' => ! $item['active'],
                       ])>{{ $item['label'] }}</a>
                @endforeach
                @if ($currentUser->is_admin)
                    <a href="{{ url('/admin') }}" class="rounded-full px-4 py-2 font-semibold text-lime hover:bg-white/10">Admin</a>
                @endif
            </nav>

            <div class="flex items-center gap-2">
                @if ($currentUser->is_admin)
                    <a href="{{ url('/admin') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-lime hover:bg-white/10 md:hidden">Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-white/25 px-3 py-2 text-sm font-semibold text-white hover:bg-white/10 sm:px-4">
                        <span class="sm:hidden">Quitter</span><span class="hidden sm:inline">Déconnexion</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Navigation mobile : onglets sous l'en-tête. --}}
        <nav aria-label="Navigation principale" class="border-t border-white/10 md:hidden">
            <div class="mx-auto flex max-w-6xl gap-1 overflow-x-auto px-3 py-2 text-sm">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" @if ($item['active']) aria-current="page" @endif
                       @class([
                           'flex-1 whitespace-nowrap rounded-full px-3 py-2 text-center font-semibold',
                           'bg-lime text-ink' => $item['active'],
                           'text-white/80' => ! $item['active'],
                       ])>{{ $item['label'] }}</a>
                @endforeach
            </div>
        </nav>
    </header>

    <main id="contenu" class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6 sm:py-10">
        @if (session('status'))
            <div class="alert mb-6" role="status">{{ session('status') }}</div>
        @endif

        {{ $slot }}
    </main>

    <footer class="border-t border-line py-6 text-center text-xs text-gray-500">
        © {{ now()->year }} Bénin Codeurs · Talent Board de la communauté VIP
    </footer>
</body>
</html>
