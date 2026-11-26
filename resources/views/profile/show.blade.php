@use('App\Enums\SkillLevel')
@use('App\Models\Profile')
@php
    $viewer = auth()->user();
    $canBrowse = $viewer->can('directory.view');
    $skillsByCategory = $profile->skills->groupBy(fn ($skill) => $skill->category?->name ?? 'Autres');
    $links = $profile->links();
@endphp
<x-layouts::app :title="$isOwner ? 'Mon profil' : $user->name">
    {{-- En-tête du profil : bande violet profond, identité en blanc. --}}
    <x-page-header>
        @if ($canBrowse && ! request()->routeIs('profile.preview'))
            <a href="{{ str_starts_with(url()->previous(), route('directory')) && url()->previous() !== url()->current() ? url()->previous() : route('directory') }}" class="-ml-2 inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-sm font-semibold text-white/80 transition hover:text-white">@svg('heroicon-m-arrow-left', 'size-4', ['aria-hidden' => 'true']) Retour à l'annuaire</a>
        @endif

        <div @class(['flex flex-col gap-5 sm:flex-row sm:items-center sm:gap-6', 'mt-4' => $canBrowse && ! request()->routeIs('profile.preview')])>
            <x-avatar :profile="$profile" :user="$user" size="size-24 sm:size-32" text="text-3xl sm:text-4xl" :pixels="320" class="ring-4 ring-white/15" />
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap gap-1.5">
                    @if ($profile->is_featured)
                        <span class="badge bg-night text-lime ring-1 ring-lime/40">@svg('heroicon-s-star', 'size-3.5', ['aria-hidden' => 'true']) À la une</span>
                    @endif
                    @if ($profile->availability)
                        <span @class(['badge', $profile->availability->badgeClasses() => $profile->availability === \App\Enums\Availability::OpenToMissions, 'bg-white/10 text-white ring-1 ring-white/20' => $profile->availability !== \App\Enums\Availability::OpenToMissions])>{{ $profile->availability->label() }}</span>
                    @endif
                </div>
                <h1 @class(['text-2xl font-semibold tracking-tight text-white sm:text-3xl', 'mt-2' => $profile->is_featured || $profile->availability])>{{ $user->name }}</h1>
                @if ($profile->headline)
                    <p class="mt-1 text-base text-white/80 sm:text-lg">{{ $profile->headline }}</p>
                @endif

                <dl class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-white/75">
                    @if ($profile->profession)
                        <div><dt class="sr-only">Métier</dt><dd class="font-semibold text-white">{{ $profile->profession->label() }}</dd></div>
                    @endif
                    @if ($profile->level)
                        <div><dt class="sr-only">Niveau</dt><dd><span class="badge bg-white/10 text-white ring-1 ring-white/20">{{ $profile->level->label() }}</span></dd></div>
                    @endif
                    @if ($profile->years_experience !== null)
                        <div><dt class="sr-only">Expérience</dt><dd>{{ $profile->years_experience === 0 ? "Moins d'un an d'expérience" : $profile->years_experience.' '.($profile->years_experience > 1 ? 'ans' : 'an')." d'expérience" }}</dd></div>
                    @endif
                    @if ($profile->city)
                        <div><dt class="sr-only">Ville</dt><dd class="inline-flex items-center gap-1">@svg('heroicon-m-map-pin', 'size-4 text-lime', ['aria-hidden' => 'true']) {{ $profile->city }}</dd></div>
                    @endif
                    @if ($profile->work_status)
                        <div>
                            <dt class="sr-only">Situation</dt>
                            <dd>
                                {{ $profile->work_status->label() }}@if ($profile->company) · {{ $profile->company }}@endif
                            </dd>
                        </div>
                    @elseif ($profile->company)
                        <div><dt class="sr-only">Entreprise</dt><dd>{{ $profile->company }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>
    </x-page-header>

    <div class="page-container">
    @if ($isOwner)
        <div class="mb-6 rounded-card border border-line border-l-4 border-l-violet bg-white p-4 sm:flex sm:items-center sm:justify-between sm:gap-4 sm:p-5">
            <div>
                <p class="font-semibold text-ink">Aperçu : voici ton profil tel que le voit l'équipe Bénin Codeurs.</p>
                <p class="mt-0.5 text-sm text-ink">
                    Seule l'équipe le consulte, pour proposer des missions et des opportunités.
                    @if ($profile->completion < Profile::minCompletion())
                        Ton profil est complet à {{ $profile->completion }}&nbsp;% : vise au moins {{ Profile::minCompletion() }}&nbsp;%.
                    @endif
                </p>
            </div>
            <a href="{{ route('profile.edit') }}" class="btn-primary mt-3 shrink-0 text-sm sm:mt-0">Modifier mon profil</a>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_20rem] lg:items-start">
        <div class="space-y-6">
            @if ($profile->bio)
                <section class="card">
                    <h2 class="section-title">Présentation</h2>
                    <p class="mt-3 whitespace-pre-line text-ink">{{ $profile->bio }}</p>
                </section>
            @endif

            @if ($profile->level)
                <section class="card">
                    <h2 class="section-title">Niveau : {{ $profile->level->label() }}</h2>
                    <p class="mt-2 text-sm text-muted">{{ $profile->level->description() }}</p>
                </section>
            @endif

            @if ($profile->skills->isNotEmpty())
                <section class="card">
                    <h2 class="section-title">Compétences</h2>
                    <div class="mt-4 space-y-5">
                        @foreach ($skillsByCategory as $categoryName => $categorySkills)
                            <div>
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-muted">{{ $categoryName }}</h3>
                                <ul class="mt-2 grid gap-2 sm:grid-cols-2">
                                    @foreach ($categorySkills as $skill)
                                        @php $skillLevel = SkillLevel::tryFrom($skill->pivot->level); @endphp
                                        <li @class([
                                            'flex items-center justify-between gap-3 rounded-lg px-3 py-2.5',
                                            'bg-lime/15 ring-1 ring-lime/70' => $skill->pivot->is_highlighted,
                                            'bg-paper ring-1 ring-line' => ! $skill->pivot->is_highlighted,
                                        ])>
                                            <span class="min-w-0 font-semibold text-ink">
                                                @if ($skill->pivot->is_highlighted) @svg('heroicon-s-star', 'inline-block size-3.5 -mt-0.5 text-violet', ['aria-hidden' => 'true'])<span class="sr-only">Mise en avant :</span> @endif
                                                {{ $skill->name }}
                                            </span>
                                            @if ($skillLevel)
                                                <span class="flex shrink-0 items-center gap-2 text-xs text-muted">
                                                    {{ $skillLevel->label() }}
                                                    <x-level-dots :level="$skillLevel" />
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($profile->projects->isNotEmpty())
                <section class="card">
                    <h2 class="section-title">Réalisations</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($profile->projects as $project)
                            <article class="flex flex-col overflow-hidden rounded-xl border border-line">
                                @if ($project->image_path)
                                    <img src="{{ $project->imageUrl(800, 500) }}" alt="Visuel du projet {{ $project->title }}" width="800" height="500" loading="lazy" decoding="async" class="aspect-[16/10] w-full bg-paper object-cover">
                                @else
                                    <div class="grid aspect-[16/10] place-items-center media-fallback text-3xl font-semibold text-violet/60" aria-hidden="true">{{ mb_strtoupper(mb_substr($project->title, 0, 1)) }}</div>
                                @endif
                                <div class="flex flex-1 flex-col p-4">
                                    <h3 class="font-semibold text-ink">{{ $project->title }}</h3>
                                    @if ($project->description)
                                        <p class="mt-1 whitespace-pre-line text-sm text-muted">{{ $project->description }}</p>
                                    @endif
                                    @if ($project->url)
                                        <a href="{{ $project->url }}" target="_blank" rel="noopener noreferrer nofollow" class="link mt-auto inline-flex items-center gap-1 pt-3 text-sm">Voir le projet @svg('heroicon-m-arrow-up-right', 'size-4', ['aria-hidden' => 'true'])</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if (! $profile->bio && $profile->skills->isEmpty() && $profile->projects->isEmpty())
                <section class="card text-center text-sm text-muted">
                    @if ($isOwner)
                        Ton profil est encore vide. <a href="{{ route('profile.edit') }}" class="link">Complète-le</a> pour te faire connaître de l'équipe.
                    @else
                        Ce membre n'a pas encore détaillé son profil.
                    @endif
                </section>
            @endif
        </div>

        <aside class="space-y-6 lg:sticky lg:top-24">
            <section class="card">
                <h2 class="section-title">Contact</h2>
                <div class="mt-4 space-y-3">
                    @if ($user->whatsapp)
                        <a href="https://wa.me/{{ ltrim($user->whatsapp, '+') }}" target="_blank" rel="noopener noreferrer" class="btn-primary w-full sm:w-full">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" class="size-5"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.98L2 22l5.17-1.5A9.9 9.9 0 1 0 12.04 2Zm5.8 14.1c-.24.68-1.42 1.3-1.96 1.35-.5.05-1.13.24-3.8-.8-3.2-1.27-5.24-4.53-5.4-4.74-.15-.21-1.29-1.72-1.29-3.28 0-1.56.82-2.33 1.11-2.65.29-.31.63-.39.84-.39h.6c.2 0 .46-.07.72.55.27.64.9 2.2.98 2.36.08.16.13.35.03.56-.1.21-.16.34-.31.52-.16.18-.33.4-.47.54-.16.16-.32.33-.14.64.18.31.81 1.33 1.74 2.16 1.2 1.06 2.2 1.39 2.52 1.55.31.16.5.13.68-.08.18-.21.78-.91.99-1.22.2-.31.41-.26.7-.16.28.1 1.8.85 2.11 1 .31.16.52.24.6.37.07.13.07.76-.18 1.47Z"/></svg>
                            Écrire sur WhatsApp
                        </a>
                    @endif
                    <a href="mailto:{{ $user->email }}" class="btn-secondary w-full break-all text-sm">@svg('heroicon-m-envelope', 'size-4 shrink-0', ['aria-hidden' => 'true']) {{ $user->email }}</a>
                </div>
            </section>

            @if ($links !== [])
                <section class="card">
                    <h2 class="section-title">Liens</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($links as $label => $url)
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer nofollow" class="flex items-center justify-between gap-3 rounded-lg border border-line px-4 py-3 font-medium text-ink hover:border-muted/40 hover:bg-paper">
                                    {{ $label }} @svg('heroicon-m-arrow-up-right', 'size-4 text-violet', ['aria-hidden' => 'true'])
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
    </div>
</x-layouts::app>
