<!DOCTYPE html>
<html lang="fr">
<head>
    @include('layouts.partials.head')
</head>
<body class="min-h-dvh bg-paper font-sans text-gray-900 antialiased">
    <header class="bg-brand">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <x-logo />
            <nav class="flex items-center gap-2 text-sm">
                @if (auth()->user()->isApproved() || auth()->user()->is_admin)
                    <a href="{{ route('directory') }}" class="hidden rounded-full px-4 py-2 font-semibold text-white/90 hover:bg-white/10 sm:inline-flex">Annuaire</a>
                @endif
                @if (auth()->user()->is_admin)
                    <a href="{{ url('/admin') }}" class="hidden rounded-full px-4 py-2 font-semibold text-white/90 hover:bg-white/10 sm:inline-flex">Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-white/25 px-4 py-2 font-semibold text-white hover:bg-white/10">Déconnexion</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-12">
        @if (session('status'))
            <div class="alert mb-6" role="status">{{ session('status') }}</div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
