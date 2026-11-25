<!DOCTYPE html>
<html lang="fr">
<head>
    @include('layouts.partials.head')
</head>
<body class="min-h-dvh bg-night font-sans text-gray-900 antialiased">
    <div class="relative isolate flex min-h-dvh flex-col overflow-hidden">
        <div aria-hidden="true" class="pointer-events-none absolute -top-32 -right-32 -z-10 size-96 rounded-full bg-violet/40 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-40 -left-24 -z-10 size-80 rounded-full bg-lime/15 blur-3xl"></div>

        <header class="px-5 pt-6 sm:px-8">
            <x-logo />
        </header>

        <main class="flex flex-1 items-start justify-center px-4 py-8 sm:items-center sm:px-6 sm:py-12">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </main>

        <footer class="px-5 pb-6 text-center text-xs text-white/60">
            © {{ now()->year }} Bénin Codeurs · Communauté VIP
        </footer>
    </div>
</body>
</html>
