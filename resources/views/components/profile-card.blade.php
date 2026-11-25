@props(['profile'])
@php
    $user = $profile->user;
    $highlighted = $profile->highlightedSkills();
    $cardSkills = ($highlighted->isNotEmpty() ? $highlighted : $profile->skills)->take(3);
    $photoUrl = $profile->photoUrl(600);
    $initials = mb_strtoupper(mb_substr($user->first_name, 0, 1).mb_substr($user->last_name, 0, 1));
@endphp
<article {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-card border border-line bg-white transition hover:border-muted/40 hover:shadow-lg hover:shadow-night/5 focus-within:ring-4 focus-within:ring-violet/15']) }}>
    <div class="relative aspect-[4/3] overflow-hidden bg-paper">
        @if ($photoUrl)
            <img src="{{ $photoUrl }}" alt="Photo de {{ $user->name }}" width="600" height="600" loading="lazy" decoding="async"
                 class="size-full object-cover transition duration-500 group-hover:scale-[1.02] motion-reduce:transition-none">
        @else
            <span aria-hidden="true" class="media-fallback grid size-full place-items-center text-4xl font-semibold tracking-tight text-violet/70">{{ $initials }}</span>
        @endif

        @if ($profile->is_featured)
            <span class="badge absolute top-3 left-3 bg-ink text-lime">@svg('heroicon-s-star', 'size-3.5', ['aria-hidden' => 'true']) À la une</span>
        @endif
        @if ($profile->availability)
            <span class="badge absolute right-3 bottom-3 bg-white/95 text-ink shadow-sm ring-1 ring-line backdrop-blur">
                <span aria-hidden="true" @class([
                    'size-1.5 rounded-full',
                    'bg-lime ring-2 ring-lime/40' => $profile->availability === \App\Enums\Availability::OpenToMissions,
                    'bg-violet' => $profile->availability === \App\Enums\Availability::OpenToJob,
                    'bg-muted/60' => $profile->availability === \App\Enums\Availability::Unavailable,
                ])></span>
                {{ $profile->availability->label() }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <h2 class="min-w-0 truncate text-base font-semibold text-ink">
                <a href="{{ route('directory.show', $profile) }}" class="after:absolute after:inset-0 focus:outline-none">{{ $user->name }}</a>
            </h2>
            @if ($profile->level)
                <span class="badge shrink-0 bg-violet/10 text-violet">{{ $profile->level->label() }}</span>
            @endif
        </div>
        @if ($profile->headline)
            <p class="mt-1 line-clamp-2 text-sm text-muted">{{ $profile->headline }}</p>
        @endif

        @if ($cardSkills->isNotEmpty())
            <ul class="mt-3 flex flex-wrap gap-1.5" aria-label="Compétences mises en avant">
                @foreach ($cardSkills as $skill)
                    <li class="rounded-md border border-line bg-paper px-2 py-0.5 text-xs font-medium text-ink">{{ $skill->name }}</li>
                @endforeach
            </ul>
        @endif

        <div class="mt-auto flex items-center justify-between gap-2 pt-4 text-sm text-muted">
            <span class="inline-flex min-w-0 items-center gap-1.5">
                @if ($profile->city)
                    @svg('heroicon-m-map-pin', 'size-4 shrink-0 text-muted', ['aria-hidden' => 'true'])
                    <span class="truncate">{{ $profile->city }}</span>
                @endif
            </span>
            <span class="inline-flex shrink-0 items-center gap-1 font-medium text-violet opacity-0 transition group-hover:opacity-100 max-lg:opacity-100" aria-hidden="true">Voir @svg('heroicon-m-arrow-right', 'size-4', ['aria-hidden' => 'true'])</span>
        </div>
    </div>
</article>
