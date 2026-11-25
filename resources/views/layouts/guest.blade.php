<!DOCTYPE html>
<html lang="fr">
<head>
    @include('layouts.partials.head')
</head>
<body class="flex min-h-dvh flex-col bg-paper font-sans text-ink antialiased">
    <header class="border-b border-line bg-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
            <x-logo dark />
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-2 text-sm font-medium text-gray-500 hover:text-ink">
                @svg('heroicon-m-arrow-left', 'size-4', ['aria-hidden' => 'true']) <span class="hidden sm:inline">Retour à l'accueil</span><span class="sm:hidden">Accueil</span>
            </a>
        </div>
    </header>

    <main class="flex flex-1 items-start justify-center px-4 py-10 sm:items-center sm:px-6 sm:py-16">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </main>

    <x-site-footer />
</body>
</html>
