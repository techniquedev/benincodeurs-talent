@props(['profile'])
@php
    $user = $profile->user;
    $highlighted = $profile->highlightedSkills();
    $cardSkills = ($highlighted->isNotEmpty() ? $highlighted : $profile->skills)->take(3);
@endphp
<article {{ $attributes->merge(['class' => 'group relative flex flex-col rounded-card bg-white p-5 shadow-lg shadow-night/5 ring-1 ring-line transition hover:-translate-y-0.5 hover:shadow-xl hover:ring-violet/30']) }}>
    @if ($profile->is_featured)
        <span class="badge absolute top-4 right-4 bg-brand text-lime"><span aria-hidden="true">★</span> À la une</span>
    @endif

    <div class="flex items-center gap-4">
        <x-avatar :profile="$profile" :user="$user" size="size-16" :pixels="160" />
        <div class="min-w-0 {{ $profile->is_featured ? 'pr-20' : '' }}">
            <h2 class="truncate text-lg font-extrabold leading-tight text-brand">
                <a href="{{ route('directory.show', $profile) }}" class="after:absolute after:inset-0 focus:outline-none">{{ $user->name }}</a>
            </h2>
            @if ($profile->headline)
                <p class="mt-0.5 line-clamp-2 text-sm text-gray-600">{{ $profile->headline }}</p>
            @endif
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-1.5">
        @if ($profile->level)
            <span class="badge bg-violet/10 text-violet">{{ $profile->level->label() }}</span>
        @endif
        @if ($profile->availability)
            <span class="badge {{ $profile->availability->badgeClasses() }}">{{ $profile->availability->label() }}</span>
        @endif
    </div>

    @if ($cardSkills->isNotEmpty())
        <ul class="mt-4 flex flex-wrap gap-1.5" aria-label="Compétences mises en avant">
            @foreach ($cardSkills as $skill)
                <li class="rounded-lg bg-paper px-2.5 py-1 text-xs font-semibold text-brand ring-1 ring-line">{{ $skill->name }}</li>
            @endforeach
        </ul>
    @endif

    <div class="mt-auto flex items-center justify-between gap-2 pt-5 text-sm text-gray-500">
        <span class="inline-flex min-w-0 items-center gap-1.5">
            @if ($profile->city)
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-violet"><path fill-rule="evenodd" d="M9.69 18.93a.75.75 0 0 0 .62 0c.12-.06 5.94-2.98 5.94-9.18a6.25 6.25 0 1 0-12.5 0c0 6.2 5.82 9.12 5.94 9.18ZM10 12a2.25 2.25 0 1 0 0-4.5A2.25 2.25 0 0 0 10 12Z" clip-rule="evenodd"/></svg>
                <span class="truncate">{{ $profile->city }}</span>
            @endif
        </span>
        <span class="shrink-0 font-semibold text-violet group-hover:underline" aria-hidden="true">Voir le profil →</span>
    </div>
</article>
