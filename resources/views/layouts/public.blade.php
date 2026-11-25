@props(['title' => null, 'description' => null, 'overlayHeader' => false])
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth motion-reduce:scroll-auto">
<head>
    @include('layouts.partials.head', ['title' => $title])
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
</head>
<body class="flex min-h-dvh flex-col bg-paper font-sans text-ink antialiased">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-lg focus:bg-violet focus:px-4 focus:py-2 focus:font-semibold focus:text-white">Aller au contenu</a>

    <x-site-header :overlay="$overlayHeader" />

    <main id="contenu" class="flex-1">
        {{ $slot }}
    </main>

    <x-site-footer />
</body>
</html>
