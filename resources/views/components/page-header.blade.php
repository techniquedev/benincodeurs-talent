@props(['title' => null, 'description' => null, 'eyebrow' => null])
{{--
    En-tête violet profond des pages de l'espace membre, du profil, de l'annuaire et des pages légales.
    Slot « actions » : boutons à droite (sur fond sombre : .btn-lime / .btn-ghost-light). Slot par défaut : contenu libre sous le titre.
--}}
<div {{ $attributes->merge(['class' => 'glow bg-brand text-white']) }}>
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                @if ($eyebrow)
                    <p class="eyebrow-dark">{{ $eyebrow }}</p>
                @endif
                @if ($title)
                    <h1 @class(['text-2xl font-semibold tracking-tight text-balance break-words sm:text-3xl', 'mt-1.5' => $eyebrow])>{{ $title }}</h1>
                @endif
                @if ($description)
                    <p class="mt-1.5 max-w-2xl text-sm text-white/75 sm:text-base">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-col gap-2 sm:shrink-0 sm:flex-row">{{ $actions }}</div>
            @endisset
        </div>

        {{ $slot }}

        @if (session('status'))
            <div class="mt-5 rounded-lg border border-white/15 bg-white/10 px-4 py-3 text-sm text-white" role="status">{{ session('status') }}</div>
        @endif
    </div>
</div>
