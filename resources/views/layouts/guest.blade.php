<!DOCTYPE html>
<html lang="fr">
<head>
    @include('layouts.partials.head')
</head>
{{-- Connexion, inscription, mot de passe : plein écran violet nuit avec lueur, logo clair, carte blanche centrée. --}}
<body class="glow flex min-h-dvh flex-col bg-night font-sans text-ink antialiased">
    <main class="flex flex-1 flex-col items-center justify-start px-4 py-8 sm:justify-center sm:px-6 sm:py-14">
        <div class="w-full max-w-md">
            <div class="mb-6 flex justify-center sm:mb-8">
                <x-logo />
            </div>

            {{ $slot }}

            <p class="mt-6 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-2 text-sm font-medium text-white/75 transition hover:text-white">
                    @svg('heroicon-m-arrow-left', 'size-4', ['aria-hidden' => 'true']) Retour à l'accueil
                </a>
            </p>
        </div>
    </main>

    <footer class="px-4 pb-6 text-center text-xs text-white/60">© {{ now()->year }} Bénin Codeurs · Talent Board de la communauté VIP</footer>
</body>
</html>
