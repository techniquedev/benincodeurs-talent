@use('App\Enums\SkillLevel')
@use('App\Models\Profile')
@php
    $viewer = auth()->user();
    $canBrowse = $viewer->isApproved() || $viewer->is_admin;
    $showCompany = $profile->company && ($profile->company_visible || $isOwner);
    $showWhatsapp = $user->whatsapp && ($profile->whatsapp_visible || $isOwner);
    $showEmail = $profile->email_visible || $isOwner;
    $skillsByCategory = $profile->skills->groupBy(fn ($skill) => $skill->category?->name ?? 'Autres');
    $links = $profile->links();
@endphp
<x-layouts::app :title="$isOwner ? 'Mon profil' : $user->name">
    @if ($canBrowse && ! request()->routeIs('profile.preview'))
        <a href="{{ str_starts_with(url()->previous(), route('directory')) && url()->previous() !== url()->current() ? url()->previous() : route('directory') }}" class="link inline-flex items-center gap-1 text-sm">@svg('heroicon-m-arrow-left', 'size-4', ['aria-hidden' => 'true']) Retour à l'annuaire</a>
    @endif

    @if ($isOwner)
        <div @class([
            'rounded-card border p-4 sm:flex sm:items-center sm:justify-between sm:gap-4 sm:p-5',
            'border-line border-l-4 border-l-lime bg-white' => $profile->isListed(),
            'border-line border-l-4 border-l-violet bg-white' => ! $profile->isListed(),
            'mt-4' => $canBrowse && ! request()->routeIs('profile.preview'),
        ])>
            <div>
                <p class="font-semibold text-ink">Aperçu : voici ce que voient les autres membres.</p>
                <p class="mt-0.5 text-sm text-gray-700">
                    @if ($profile->isListed())
                        Ton profil est visible dans l'annuaire.
                    @elseif ($profile->completion < Profile::minCompletion())
                        Ton profil est complet à {{ $profile->completion }}&nbsp;% : il apparaîtra dans l'annuaire à partir de {{ Profile::minCompletion() }}&nbsp;%.
                    @else
                        Ton profil apparaîtra dans l'annuaire dès que ton compte sera validé par l'équipe.
                    @endif
                    Les éléments masqués ne sont visibles que par toi.
                </p>
            </div>
            <a href="{{ route('profile.edit') }}" class="btn-primary mt-3 shrink-0 text-sm sm:mt-0">Modifier mon profil</a>
        </div>
    @endif

    {{-- En-tête du profil --}}
    <section class="mt-4 overflow-hidden rounded-card border border-line bg-white">
        <div class="h-24 bg-linear-to-r from-violet/15 via-paper to-lime/30 sm:h-32"></div>
        <div class="px-5 pb-6 sm:px-8">
            <div class="-mt-12 flex flex-col gap-4 sm:-mt-16 sm:flex-row sm:items-end sm:justify-between">
                <x-avatar :profile="$profile" :user="$user" size="size-24 sm:size-32" text="text-3xl sm:text-4xl" :pixels="320" class="ring-4 ring-white" />
                <div class="flex flex-wrap gap-1.5 sm:justify-end">
                    @if ($profile->is_featured)
                        <span class="badge bg-ink text-lime">@svg('heroicon-s-star', 'size-3.5', ['aria-hidden' => 'true']) À la une</span>
                    @endif
                    @if ($profile->availability)
                        <span class="badge {{ $profile->availability->badgeClasses() }}">{{ $profile->availability->label() }}</span>
                    @endif
                </div>
            </div>

            <h1 class="mt-4 text-2xl font-semibold tracking-tight text-ink sm:text-3xl">{{ $user->name }}</h1>
            @if ($profile->headline)
                <p class="mt-1 text-base text-gray-700 sm:text-lg">{{ $profile->headline }}</p>
            @endif

            <dl class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600">
                @if ($profile->profession)
                    <div><dt class="sr-only">Métier</dt><dd class="font-semibold text-ink">{{ $profile->profession->label() }}</dd></div>
                @endif
                @if ($profile->level)
                    <div><dt class="sr-only">Niveau</dt><dd><span class="badge bg-violet/10 text-violet">{{ $profile->level->label() }}</span></dd></div>
                @endif
                @if ($profile->years_experience !== null)
                    <div><dt class="sr-only">Expérience</dt><dd>{{ $profile->years_experience === 0 ? "Moins d'un an d'expérience" : $profile->years_experience.' '.($profile->years_experience > 1 ? 'ans' : 'an')." d'expérience" }}</dd></div>
                @endif
                @if ($profile->city)
                    <div><dt class="sr-only">Ville</dt><dd class="inline-flex items-center gap-1">@svg('heroicon-m-map-pin', 'size-4 text-violet', ['aria-hidden' => 'true']) {{ $profile->city }}</dd></div>
                @endif
                @if ($profile->work_status)
                    <div>
                        <dt class="sr-only">Situation</dt>
                        <dd>
                            {{ $profile->work_status->label() }}@if ($showCompany) · {{ $profile->company }}@endif
                            @if ($showCompany && ! $profile->company_visible) <span class="text-xs text-gray-400">(entreprise masquée)</span> @endif
                        </dd>
                    </div>
                @elseif ($showCompany)
                    <div><dt class="sr-only">Entreprise</dt><dd>{{ $profile->company }} @if (! $profile->company_visible) <span class="text-xs text-gray-400">(masquée)</span> @endif</dd></div>
                @endif
            </dl>
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_20rem] lg:items-start">
        <div class="space-y-6">
            @if ($profile->bio)
                <section class="card">
                    <h2 class="section-title">Présentation</h2>
                    <p class="mt-3 whitespace-pre-line text-gray-700">{{ $profile->bio }}</p>
                </section>
            @endif

            @if ($profile->level)
                <section class="card">
                    <h2 class="section-title">Niveau : {{ $profile->level->label() }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ $profile->level->description() }}</p>
                </section>
            @endif

            @if ($profile->skills->isNotEmpty())
                <section class="card">
                    <h2 class="section-title">Compétences</h2>
                    <div class="mt-4 space-y-5">
                        @foreach ($skillsByCategory as $categoryName => $categorySkills)
                            <div>
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $categoryName }}</h3>
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
                                                <span class="flex shrink-0 items-center gap-2 text-xs text-gray-600">
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
                                        <p class="mt-1 whitespace-pre-line text-sm text-gray-600">{{ $project->description }}</p>
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
                <section class="card text-center text-sm text-gray-600">
                    @if ($isOwner)
                        Ton profil est encore vide. <a href="{{ route('profile.edit') }}" class="link">Complète-le</a> pour apparaître dans l'annuaire.
                    @else
                        Ce membre n'a pas encore détaillé son profil.
                    @endif
                </section>
            @endif
        </div>

        <aside class="space-y-6 lg:sticky lg:top-6">
            <section class="card">
                <h2 class="section-title">Contact</h2>
                @if ($showWhatsapp || $showEmail)
                    <div class="mt-4 space-y-3">
                        @if ($showWhatsapp)
                            <a href="https://wa.me/{{ ltrim($user->whatsapp, '+') }}" target="_blank" rel="noopener noreferrer" class="btn-primary w-full sm:w-full">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" class="size-5"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.98L2 22l5.17-1.5A9.9 9.9 0 1 0 12.04 2Zm5.8 14.1c-.24.68-1.42 1.3-1.96 1.35-.5.05-1.13.24-3.8-.8-3.2-1.27-5.24-4.53-5.4-4.74-.15-.21-1.29-1.72-1.29-3.28 0-1.56.82-2.33 1.11-2.65.29-.31.63-.39.84-.39h.6c.2 0 .46-.07.72.55.27.64.9 2.2.98 2.36.08.16.13.35.03.56-.1.21-.16.34-.31.52-.16.18-.33.4-.47.54-.16.16-.32.33-.14.64.18.31.81 1.33 1.74 2.16 1.2 1.06 2.2 1.39 2.52 1.55.31.16.5.13.68-.08.18-.21.78-.91.99-1.22.2-.31.41-.26.7-.16.28.1 1.8.85 2.11 1 .31.16.52.24.6.37.07.13.07.76-.18 1.47Z"/></svg>
                                Écrire sur WhatsApp
                            </a>
                            @if ($isOwner && ! $profile->whatsapp_visible)
                                <p class="text-xs text-gray-500">WhatsApp masqué pour les autres membres.</p>
                            @endif
                        @endif
                        @if ($showEmail)
                            <a href="mailto:{{ $user->email }}" class="btn-secondary w-full break-all text-sm">@svg('heroicon-m-envelope', 'size-4 shrink-0', ['aria-hidden' => 'true']) {{ $user->email }}</a>
                            @if ($isOwner && ! $profile->email_visible)
                                <p class="text-xs text-gray-500">E-mail masqué pour les autres membres.</p>
                            @endif
                        @endif
                    </div>
                @else
                    <p class="mt-3 text-sm text-gray-600">Ce membre n'affiche pas ses coordonnées.@if ($links !== []) Tu peux le joindre par ses liens ci-dessous.@endif</p>
                @endif
            </section>

            @if ($links !== [])
                <section class="card">
                    <h2 class="section-title">Liens</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($links as $label => $url)
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer nofollow" class="flex items-center justify-between gap-3 rounded-lg border border-line px-4 py-3 font-medium text-ink hover:border-gray-300 hover:bg-gray-50">
                                    {{ $label }} @svg('heroicon-m-arrow-up-right', 'size-4 text-violet', ['aria-hidden' => 'true'])
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
</x-layouts::app>
