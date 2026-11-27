@use('App\Enums\Availability')
@use('App\Enums\ExperienceLevel')
@use('App\Enums\Profession')
@use('App\Enums\SkillLevel')
@use('App\Enums\WorkStatus')
@use('App\Models\Profile')
@php
    $checklist = collect($this->checklist);
    $stepDone = fn (int $n) => $checklist->where('step', $n)->every(fn ($item) => $item['done']);
    $user = auth()->user();
    $total = count($steps);
@endphp

<div x-data x-on:profile-step-changed.window="window.scrollTo({ top: 0, behavior: 'smooth' })">
    <x-page-header :eyebrow="'Étape '.$step.' sur '.$total" title="Mon profil">
        <x-slot:actions>
            <a href="{{ route('profile.preview') }}" class="btn-ghost-light px-4 py-2 text-sm">Voir mon profil</a>
        </x-slot:actions>
    </x-page-header>

    <div class="page-container grid grid-cols-1 gap-6 lg:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
        {{-- Colonne latérale : jauge, étapes, éléments manquants --}}
        <aside class="min-w-0 space-y-4 lg:sticky lg:top-24">
            <div class="card flex items-center gap-4 p-5 sm:p-5 lg:flex-col lg:text-center">
                <x-completion-ring :value="$completion" :threshold="$minCompletion" class="size-20 lg:size-28" />
                <div class="min-w-0">
                    <p class="font-semibold text-ink">Profil complet à {{ $completion }}&nbsp;%</p>
                    @if ($completion >= $minCompletion)
                        <p class="mt-1 text-sm text-muted">Ton profil est complet : l'équipe Bénin Codeurs connaît tes compétences.</p>
                    @else
                        <p class="mt-1 text-sm text-muted">Encore {{ $minCompletion - $completion }}&nbsp;% pour un profil complet ({{ $minCompletion }}&nbsp;% minimum).</p>
                    @endif
                </div>
            </div>

            {{-- Étapes : onglets défilants sur mobile, liste sur grand écran --}}
            <nav aria-label="Étapes du profil" class="-mx-4 overflow-x-auto overscroll-x-contain px-4 lg:mx-0 lg:overflow-visible lg:px-0">
                <ol class="flex gap-2 pb-1 lg:flex-col lg:gap-0.5 lg:rounded-card lg:border lg:border-line lg:bg-white lg:p-2">
                    @foreach ($steps as $number => $label)
                        <li class="shrink-0">
                            <button type="button" wire:click="goToStep({{ $number }})" @if ($number === $step) aria-current="step" @endif
                                    @class([
                                        'flex w-full items-center gap-2.5 rounded-lg border px-3 py-2 text-left text-sm font-medium transition lg:border-0',
                                        'border-violet bg-violet text-white lg:bg-violet/8 lg:text-violet' => $number === $step,
                                        'border-line bg-white text-ink hover:bg-paper lg:bg-transparent' => $number !== $step,
                                    ])>
                                <span @class([
                                    'grid size-6 shrink-0 place-items-center rounded-full text-xs font-bold',
                                    'bg-lime text-ink' => $stepDone($number),
                                    'bg-white/25 text-white lg:bg-violet lg:text-white' => ! $stepDone($number) && $number === $step,
                                    'bg-line text-muted' => ! $stepDone($number) && $number !== $step,
                                ])>
                                    @if ($stepDone($number)) @svg('heroicon-m-check', 'size-4', ['aria-hidden' => 'true']) @else {{ $number }} @endif
                                </span>
                                <span class="whitespace-nowrap">{{ $label }}</span>
                            </button>
                        </li>
                    @endforeach
                </ol>
            </nav>

            @php $missing = $checklist->where('done', false); @endphp
            @if ($missing->isNotEmpty())
                <div class="card hidden p-5 sm:p-5 lg:block">
                    <p class="text-sm font-semibold text-ink">Pour compléter ton profil</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($missing as $item)
                            <li>
                                <button type="button" wire:click="goToStep({{ $item['step'] }})" class="flex w-full items-center justify-between gap-2 text-left text-ink hover:text-violet">
                                    <span>{{ $item['label'] }}</span>
                                    <span class="text-xs font-bold text-violet">+{{ $item['weight'] }}&nbsp;%</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>

        {{-- Formulaire de l'étape en cours --}}
        <form wire:submit="saveAndContinue" class="card min-w-0" novalidate>
            <h2 class="text-xl font-semibold tracking-tight text-ink">{{ $steps[$step] }}</h2>

            {{-- 1. Identité --}}
            @if ($step === 1)
                <p class="mt-1 text-sm text-muted">Ce que l'équipe Bénin Codeurs voit en premier.</p>

                <div class="mt-6 space-y-5">
                    <div>
                        <span class="field-label">Photo</span>
                        <div class="flex flex-wrap items-center gap-4">
                            @if ($photo && method_exists($photo, 'isPreviewable') && $photo->isPreviewable())
                                <img src="{{ $photo->temporaryUrl() }}" alt="Aperçu de la nouvelle photo" class="size-20 rounded-full object-cover sm:size-24">
                            @else
                                <x-avatar :profile="$this->profile" :user="$user" size="size-20 sm:size-24" text="text-2xl" :pixels="200" />
                            @endif
                            <div class="flex min-w-0 flex-col gap-2">
                                <label class="btn-secondary cursor-pointer text-sm">
                                    <input type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp" class="sr-only">
                                    {{ $this->profile->photo_path || $photo ? 'Changer la photo' : 'Choisir une photo' }}
                                </label>
                                @if ($this->profile->photo_path && ! $photo)
                                    <button type="button" wire:click="removePhoto" wire:confirm="Supprimer ta photo ?" class="text-left text-sm font-semibold text-red-700 hover:underline">Supprimer</button>
                                @endif
                            </div>
                        </div>
                        <p class="field-help" wire:loading.remove wire:target="photo">JPG, PNG ou WebP, 5 Mo maximum. Une photo nette, de face, de préférence.</p>
                        <p class="field-help text-violet" wire:loading wire:target="photo">Envoi de la photo…</p>
                        @error('photo') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="headline" class="field-label">Titre professionnel</label>
                        <input wire:model="headline" id="headline" type="text" maxlength="120" class="field-input" placeholder="Ex. Développeuse Laravel et React">
                        @error('headline') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="city" class="field-label">Ville</label>
                        <input wire:model="city" id="city" type="text" maxlength="100" list="cities" autocomplete="address-level2" class="field-input" placeholder="Ex. Cotonou">
                        <datalist id="cities">
                            @foreach (['Cotonou', 'Abomey-Calavi', 'Porto-Novo', 'Parakou', 'Bohicon', 'Abomey', 'Ouidah', 'Lokossa', 'Natitingou', 'Djougou', 'Kandi', 'Allada', 'Sèmè-Kpodji'] as $cityName)
                                <option value="{{ $cityName }}">
                            @endforeach
                        </datalist>
                        @error('city') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ count: {{ mb_strlen($bio) }} }">
                        <label for="bio" class="field-label">Présentation</label>
                        <textarea wire:model="bio" id="bio" rows="5" maxlength="{{ Profile::BIO_MAX_LENGTH }}" x-on:input="count = $event.target.value.length"
                                  class="field-input" placeholder="Qui es-tu, sur quoi travailles-tu, qu'est-ce qui te motive ?"></textarea>
                        <p class="field-help text-right"><span x-text="count">{{ mb_strlen($bio) }}</span> / {{ Profile::BIO_MAX_LENGTH }}</p>
                        @error('bio') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            {{-- 2. Métier et niveau --}}
            @if ($step === 2)
                <p class="mt-1 text-sm text-muted">Ton métier principal et où tu en es aujourd'hui.</p>

                <div class="mt-6 space-y-6">
                    <fieldset>
                        <legend class="field-label">Métier principal</legend>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach (Profession::cases() as $option)
                                <label @class([
                                    'flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 text-sm font-semibold transition',
                                    'border-violet bg-violet/5 text-ink ring-2 ring-violet/20' => $profession === $option->value,
                                    'border-line text-ink hover:border-violet/40' => $profession !== $option->value,
                                ])>
                                    <input type="radio" wire:model.live="profession" value="{{ $option->value }}" class="size-4 shrink-0 accent-violet">
                                    {{ $option->label() }}
                                </label>
                            @endforeach
                        </div>
                        @error('profession') <p class="field-error">{{ $message }}</p> @enderror
                    </fieldset>

                    <fieldset>
                        <legend class="field-label">Niveau</legend>
                        <div class="grid gap-2">
                            @foreach (ExperienceLevel::cases() as $option)
                                <label @class([
                                    'flex cursor-pointer items-start gap-3 rounded-lg border px-4 py-3 transition',
                                    'border-violet bg-violet/5 ring-2 ring-violet/20' => $level === $option->value,
                                    'border-line hover:border-violet/40' => $level !== $option->value,
                                ])>
                                    <input type="radio" wire:model.live="level" value="{{ $option->value }}" class="mt-1 size-4 shrink-0 accent-violet">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-ink">{{ $option->label() }}</span>
                                        <span class="block text-sm text-muted">{{ $option->description() }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('level') <p class="field-error">{{ $message }}</p> @enderror
                    </fieldset>

                    <div class="w-full max-w-xs">
                        <label for="years_experience" class="field-label">Années d'expérience</label>
                        <input wire:model="years_experience" id="years_experience" type="number" min="0" max="50" inputmode="numeric" class="field-input" placeholder="Ex. 3">
                        <p class="field-help">Mets 0 si tu débutes.</p>
                        @error('years_experience') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            {{-- 3. Compétences : recherche avec suggestions, suggestions selon le métier, catégories en accordéon, liste choisie. --}}
            @if ($step === 3)
                @php
                    $catalog = collect($this->skillCatalog);
                    $skillNames = $catalog->pluck('name', 'id');
                    $skillCategories = $catalog->pluck('category', 'id');
                    $selectedIds = collect($skills)->pluck('id')->map(fn ($id) => (int) $id)->all();
                    $highlightedCount = collect($skills)->where('highlighted', true)->count();
                    $isFull = count($skills) >= Profile::MAX_SKILLS;
                    $chosenProfession = Profession::tryFrom($profession);
                @endphp
                <p class="mt-1 text-sm text-muted">
                    Choisis jusqu'à {{ Profile::MAX_SKILLS }} compétences et indique ton niveau.
                    Mets-en {{ Profile::MAX_HIGHLIGHTED_SKILLS }} en avant avec l'étoile : ce sont celles que l'équipe voit en premier.
                </p>

                @error('skills') <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</div> @enderror

                {{-- Recherche avec suggestions (combobox) --}}
                <div class="relative mt-6"
                     x-data="{
                        q: '',
                        open: false,
                        active: 0,
                        catalog: @js($this->skillCatalog),
                        get selectedIds() { return (this.$wire.skills ?? []).map((skill) => Number(skill.id)) },
                        get results() {
                            const term = this.q.trim().toLowerCase();
                            if (term === '') { return [] }
                            return this.catalog.filter((skill) => skill.name.toLowerCase().includes(term) && ! this.selectedIds.includes(skill.id)).slice(0, 8);
                        },
                        move(step) { if (this.results.length) { this.open = true; this.active = (this.active + step + this.results.length) % this.results.length } },
                        choose(skill) {
                            if (! skill) { return }
                            this.$wire.addSkill(skill.id);
                            this.q = '';
                            this.open = false;
                            this.active = 0;
                            this.$refs.input.focus();
                        },
                     }"
                     x-on:click.outside="open = false">
                    <label for="skill-search" class="field-label">Rechercher une compétence</label>
                    <div class="relative">
                        @svg('heroicon-m-magnifying-glass', 'pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-muted', ['aria-hidden' => 'true'])
                        <input id="skill-search" x-ref="input" type="search" autocomplete="off" class="field-input pl-10"
                               placeholder="Ex. Laravel, Figma, Power BI…"
                               role="combobox" aria-autocomplete="list" aria-controls="skill-search-results"
                               x-bind:aria-expanded="(open && q.trim() !== '').toString()"
                               x-bind:aria-activedescendant="open && results.length ? 'skill-option-' + results[active].id : null"
                               x-model="q"
                               x-on:input="open = true; active = 0"
                               x-on:focus="open = q.trim() !== ''"
                               x-on:keydown.arrow-down.prevent="move(1)"
                               x-on:keydown.arrow-up.prevent="move(-1)"
                               x-on:keydown.enter.prevent="choose(results[active])"
                               x-on:keydown.escape="open = false"
                               @disabled($isFull)>
                    </div>
                    @if ($isFull)
                        <p class="field-help">Tu as choisi {{ Profile::MAX_SKILLS }} compétences, le maximum. Retire-en une pour en ajouter une autre.</p>
                    @endif

                    <div x-show="open && q.trim() !== ''" x-cloak class="absolute inset-x-0 top-full z-20 mt-1 overflow-hidden rounded-lg border border-line bg-white shadow-lg">
                        <ul id="skill-search-results" role="listbox" aria-label="Suggestions" class="max-h-72 overflow-y-auto py-1">
                            <template x-for="(skill, index) in results" x-bind:key="skill.id">
                                <li role="option" x-bind:id="'skill-option-' + skill.id" x-bind:aria-selected="(index === active).toString()"
                                    x-on:mousedown.prevent="choose(skill)" x-on:mouseenter="active = index"
                                    x-bind:class="index === active ? 'bg-violet/8' : ''"
                                    class="flex min-h-11 cursor-pointer items-center justify-between gap-3 px-4 py-2 text-sm">
                                    <span class="min-w-0 font-medium text-ink" x-text="skill.name"></span>
                                    <span class="shrink-0 text-right text-xs text-muted" x-text="skill.category"></span>
                                </li>
                            </template>
                        </ul>
                        <p x-show="results.length === 0" class="px-4 py-3 text-sm text-muted">
                            Aucune compétence trouvée (ou déjà choisie). Parcours les catégories ci-dessous.
                        </p>
                    </div>
                </div>

                {{-- 8 suggestions selon le métier --}}
                @if ($this->suggestedSkills->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-ink">
                            {{ $chosenProfession ? 'Suggestions pour ton métier : '.$chosenProfession->label() : 'Compétences souvent choisies' }}
                        </h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($this->suggestedSkills as $suggestion)
                                @php $isSelected = in_array($suggestion->id, $selectedIds, true); @endphp
                                <button type="button" wire:key="suggestion-{{ $suggestion->id }}" wire:click="toggleSkill({{ $suggestion->id }})"
                                        aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                                        @disabled($isFull && ! $isSelected)
                                        @class([
                                            'chip min-h-10 disabled:cursor-not-allowed disabled:opacity-50',
                                            'border-violet bg-violet text-white' => $isSelected,
                                            'border-line bg-white text-ink hover:border-violet hover:text-violet' => ! $isSelected,
                                        ])>
                                    @if ($isSelected) @svg('heroicon-m-check', 'size-4', ['aria-hidden' => 'true']) @else @svg('heroicon-m-plus', 'size-4', ['aria-hidden' => 'true']) @endif
                                    {{ $suggestion->name }}
                                </button>
                            @endforeach
                        </div>
                        @unless ($chosenProfession)
                            <p class="field-help">Choisis ton métier à l'étape 2 pour des suggestions adaptées.</p>
                        @endunless
                    </div>
                @endif

                {{-- Compétences choisies --}}
                <div class="mt-8">
                    <div class="flex flex-wrap items-center justify-between gap-x-2 gap-y-1">
                        <h3 class="text-sm font-semibold text-ink">Mes compétences</h3>
                        <p class="text-xs font-semibold text-muted">{{ count($skills) }}/{{ Profile::MAX_SKILLS }} · {{ $highlightedCount }}/{{ Profile::MAX_HIGHLIGHTED_SKILLS }} en avant</p>
                    </div>

                    @if ($skills === [])
                        <p class="mt-3 rounded-lg border border-dashed border-line px-4 py-6 text-center text-sm text-muted">Aucune compétence choisie. Recherche-en une ou touche une suggestion.</p>
                    @else
                        <ul class="mt-3 divide-y divide-line rounded-lg border border-line bg-white">
                            @foreach ($skills as $skill)
                                @php
                                    $skillId = (int) $skill['id'];
                                    $skillName = $skillNames[$skillId] ?? 'Compétence retirée';
                                    $currentLevel = SkillLevel::tryFrom($skill['level']) ?? SkillLevel::Intermediate;
                                    $canHighlight = $skill['highlighted'] || $highlightedCount < Profile::MAX_HIGHLIGHTED_SKILLS;
                                @endphp
                                <li wire:key="selected-{{ $skillId }}" class="px-3 py-3 sm:px-4">
                                    <div class="flex items-center gap-3">
                                        <button type="button" wire:click="toggleHighlight({{ $skillId }})"
                                                aria-pressed="{{ $skill['highlighted'] ? 'true' : 'false' }}"
                                                @disabled(! $canHighlight)
                                                title="{{ $skill['highlighted'] ? 'Retirer de la mise en avant' : ($canHighlight ? 'Mettre en avant' : Profile::MAX_HIGHLIGHTED_SKILLS.' compétences en avant au maximum') }}"
                                                @class([
                                                    'grid size-10 shrink-0 place-items-center rounded-full transition disabled:cursor-not-allowed disabled:opacity-40',
                                                    'bg-violet text-white' => $skill['highlighted'],
                                                    'bg-paper text-muted hover:text-violet' => ! $skill['highlighted'],
                                                ])>
                                            @svg($skill['highlighted'] ? 'heroicon-s-star' : 'heroicon-o-star', 'size-5', ['aria-hidden' => 'true'])
                                            <span class="sr-only">{{ $skill['highlighted'] ? 'Retirer '.$skillName.' de la mise en avant' : 'Mettre '.$skillName.' en avant' }}</span>
                                        </button>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate font-semibold text-ink">{{ $skillName }}</p>
                                            @if ($skillCategories[$skillId] ?? null)
                                                <p class="truncate text-xs text-muted">{{ $skillCategories[$skillId] }}</p>
                                            @endif
                                        </div>
                                        <button type="button" wire:click="removeSkill({{ $skillId }})" class="grid size-10 shrink-0 place-items-center rounded-full text-muted hover:bg-red-50 hover:text-red-700">
                                            @svg('heroicon-m-x-mark', 'size-5', ['aria-hidden' => 'true'])<span class="sr-only">Retirer {{ $skillName }}</span>
                                        </button>
                                    </div>

                                    {{-- Niveau en 4 crans --}}
                                    <fieldset class="mt-3 sm:pl-13">
                                        <legend class="sr-only">Niveau en {{ $skillName }}</legend>
                                        <div class="grid grid-cols-4 gap-1.5">
                                            @foreach (SkillLevel::cases() as $option)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="skill-level-{{ $skillId }}" value="{{ $option->value }}" class="peer sr-only"
                                                           @checked($option === $currentLevel)
                                                           wire:change="setSkillLevel({{ $skillId }}, '{{ $option->value }}')">
                                                    <span @class([
                                                        'block h-1.5 rounded-full transition peer-focus-visible:ring-2 peer-focus-visible:ring-violet/40',
                                                        'bg-violet' => $option->rank() <= $currentLevel->rank(),
                                                        'bg-line' => $option->rank() > $currentLevel->rank(),
                                                    ])></span>
                                                    <span @class([
                                                        'mt-1.5 block rounded-md py-1 text-center text-[11px] leading-tight hyphens-auto break-words transition sm:text-xs peer-focus-visible:outline-2 peer-focus-visible:outline-violet',
                                                        'font-semibold text-violet' => $option === $currentLevel,
                                                        'text-muted hover:text-ink' => $option !== $currentLevel,
                                                    ])>{{ $option->label() }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Parcourir par catégorie : accordéon --}}
                <div class="mt-8">
                    <h3 class="text-sm font-semibold text-ink">Parcourir par catégorie</h3>
                    <div class="mt-3 space-y-2">
                        @foreach ($this->categories as $category)
                            @php $selectedInCategory = $category->skills->whereIn('id', $selectedIds)->count(); @endphp
                            <details wire:key="category-{{ $category->id }}" wire:ignore.self class="group rounded-lg border border-line bg-white">
                                <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-semibold text-ink [&::-webkit-details-marker]:hidden">
                                    <span class="min-w-0">
                                        {{ $category->name }}
                                        <span class="ml-1 font-normal text-muted">({{ $category->skills->count() }})</span>
                                    </span>
                                    <span class="flex shrink-0 items-center gap-2">
                                        @if ($selectedInCategory > 0)
                                            <span class="badge bg-violet/10 text-violet">{{ $selectedInCategory }} choisie{{ $selectedInCategory > 1 ? 's' : '' }}</span>
                                        @endif
                                        @svg('heroicon-m-chevron-down', 'size-5 text-muted transition group-open:rotate-180', ['aria-hidden' => 'true'])
                                    </span>
                                </summary>
                                <div class="flex flex-wrap gap-2 border-t border-line px-4 py-4">
                                    @foreach ($category->skills as $skillOption)
                                        @php $isSelected = in_array($skillOption->id, $selectedIds, true); @endphp
                                        <button type="button" wire:key="skill-{{ $skillOption->id }}" wire:click="toggleSkill({{ $skillOption->id }})"
                                                aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                                                @disabled($isFull && ! $isSelected)
                                                @class([
                                                    'chip min-h-10 disabled:cursor-not-allowed disabled:opacity-50',
                                                    'border-violet bg-violet text-white' => $isSelected,
                                                    'border-line bg-white text-ink hover:border-violet hover:text-violet' => ! $isSelected,
                                                ])>
                                            @if ($isSelected) @svg('heroicon-m-check', 'size-4', ['aria-hidden' => 'true']) @else @svg('heroicon-m-plus', 'size-4', ['aria-hidden' => 'true']) @endif
                                            {{ $skillOption->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 4. Situation --}}
            @if ($step === 4)
                <p class="mt-1 text-sm text-muted">Ta situation actuelle et ce que tu recherches.</p>

                <div class="mt-6 space-y-6">
                    <fieldset>
                        <legend class="field-label">Situation</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach (WorkStatus::cases() as $option)
                                <label @class([
                                    'chip cursor-pointer px-4 py-2',
                                    'border-violet bg-violet text-white' => $work_status === $option->value,
                                    'border-line bg-white text-ink hover:border-violet' => $work_status !== $option->value,
                                ])>
                                    <input type="radio" wire:model.live="work_status" value="{{ $option->value }}" class="sr-only">
                                    {{ $option->label() }}
                                </label>
                            @endforeach
                        </div>
                        @error('work_status') <p class="field-error">{{ $message }}</p> @enderror
                    </fieldset>

                    <div>
                        <label for="company" class="field-label">Entreprise actuelle <span class="font-normal text-muted">(facultatif)</span></label>
                        <input wire:model="company" id="company" type="text" maxlength="150" class="field-input" placeholder="Ex. Bénin Codeurs">
                        @error('company') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <fieldset>
                        <legend class="field-label">Disponibilité</legend>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                            @foreach (Availability::cases() as $option)
                                <label @class([
                                    'flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 text-sm font-semibold transition',
                                    'border-violet bg-violet/5 text-ink ring-2 ring-violet/20' => $availability === $option->value,
                                    'border-line text-ink hover:border-violet/40' => $availability !== $option->value,
                                ])>
                                    <input type="radio" wire:model.live="availability" value="{{ $option->value }}" class="size-4 shrink-0 accent-violet">
                                    {{ $option->label() }}
                                </label>
                            @endforeach
                        </div>
                        @error('availability') <p class="field-error">{{ $message }}</p> @enderror
                    </fieldset>
                </div>
            @endif

            {{-- 5. Réalisations --}}
            @if ($step === 5)
                <p class="mt-1 text-sm text-muted">Jusqu'à {{ Profile::MAX_PROJECTS }} projets dont tu es fier : site, application, maquette, campagne, étude…</p>

                <div class="mt-6 space-y-5">
                    @forelse ($projects as $i => $project)
                        @php $upload = $projectImages[$project['key']] ?? null; @endphp
                        <fieldset wire:key="project-{{ $project['key'] }}" class="rounded-lg border border-line p-4 sm:p-5">
                            <div class="flex items-center justify-between gap-2">
                                <legend class="text-sm font-semibold text-ink">Réalisation {{ $i + 1 }}</legend>
                                <button type="button" wire:click="removeProject({{ $i }})" wire:confirm="Retirer cette réalisation ? Elle sera supprimée à l'enregistrement." class="text-sm font-semibold text-red-700 hover:underline">Retirer</button>
                            </div>

                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="project-title-{{ $i }}" class="field-label">Titre</label>
                                    <input wire:model="projects.{{ $i }}.title" id="project-title-{{ $i }}" type="text" maxlength="120" class="field-input" placeholder="Ex. Application de livraison pour un restaurant">
                                    @error("projects.$i.title") <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="project-description-{{ $i }}" class="field-label">Description</label>
                                    <textarea wire:model="projects.{{ $i }}.description" id="project-description-{{ $i }}" rows="3" maxlength="500" class="field-input" placeholder="Ton rôle, les outils utilisés, le résultat."></textarea>
                                    @error("projects.$i.description") <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="project-url-{{ $i }}" class="field-label">Lien</label>
                                    <input wire:model="projects.{{ $i }}.url" id="project-url-{{ $i }}" type="text" inputmode="url" class="field-input" placeholder="https://…">
                                    @error("projects.$i.url") <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <span class="field-label">Image</span>
                                    <div class="flex flex-wrap items-center gap-4">
                                        @if ($upload && method_exists($upload, 'isPreviewable') && $upload->isPreviewable())
                                            <img src="{{ $upload->temporaryUrl() }}" alt="" class="h-20 w-32 max-w-full rounded-xl object-cover">
                                        @elseif ($project['image_path'])
                                            <img src="{{ \App\Support\Media::url($project['image_path'], 320, 200) }}" alt="" class="h-20 w-32 max-w-full rounded-xl object-cover">
                                        @endif
                                        <label class="btn-secondary cursor-pointer text-sm">
                                            <input type="file" wire:model="projectImages.{{ $project['key'] }}" accept="image/jpeg,image/png,image/webp" class="sr-only">
                                            {{ $upload || $project['image_path'] ? "Changer l'image" : 'Ajouter une image' }}
                                        </label>
                                        @if ($upload || $project['image_path'])
                                            <button type="button" wire:click="removeProjectImage({{ $i }})" class="text-sm font-semibold text-red-700 hover:underline">Retirer l'image</button>
                                        @endif
                                    </div>
                                    <p class="field-help" wire:loading.remove wire:target="projectImages.{{ $project['key'] }}">Capture d'écran ou visuel, 5 Mo maximum.</p>
                                    <p class="field-help text-violet" wire:loading wire:target="projectImages.{{ $project['key'] }}">Envoi de l'image…</p>
                                    @error('projectImages.'.$project['key']) <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </fieldset>
                    @empty
                        <p class="rounded-lg border border-dashed border-line px-4 py-6 text-center text-sm text-muted">Aucune réalisation pour l'instant.</p>
                    @endforelse

                    @if (count($projects) < Profile::MAX_PROJECTS)
                        <button type="button" wire:click="addProject" class="btn-secondary w-full border-dashed">+ Ajouter une réalisation</button>
                    @endif
                </div>
            @endif

            {{-- 6. Liens et contact --}}
            @if ($step === 6)
                <p class="mt-1 text-sm text-muted">Où l'équipe peut voir ton travail en ligne.</p>

                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @foreach (['linkedin_url' => ['LinkedIn', 'linkedin.com/in/ton-nom'], 'github_url' => ['GitHub', 'github.com/ton-pseudo'], 'portfolio_url' => ['Portfolio', 'ton-site.com'], 'behance_url' => ['Behance', 'behance.net/ton-pseudo']] as $field => [$label, $placeholder])
                        <div>
                            <label for="{{ $field }}" class="field-label">{{ $label }}</label>
                            <input wire:model="{{ $field }}" id="{{ $field }}" type="text" inputmode="url" autocomplete="url" class="field-input" placeholder="{{ $placeholder }}">
                            @error($field) <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 rounded-lg border border-line bg-paper px-4 py-3 text-sm">
                    <p class="font-semibold text-ink">Tes coordonnées</p>
                    <p class="mt-1 text-muted">
                        WhatsApp : {{ $user->whatsapp ?: 'aucun numéro enregistré' }} · E-mail : <span class="break-all">{{ $user->email }}</span>.
                        L'équipe Bénin Codeurs les utilise pour te contacter au sujet d'une mission ou d'une opportunité.
                    </p>
                </div>
            @endif

            {{-- Navigation entre étapes --}}
            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                @if ($step > 1)
                    <button type="button" wire:click="goToStep({{ $step - 1 }})" class="btn-secondary w-full sm:w-auto">@svg('heroicon-m-arrow-left', 'size-4', ['aria-hidden' => 'true']) Précédent</button>
                @else
                    <span class="hidden sm:block"></span>
                @endif
                <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="saveAndContinue,goToStep,photo,projectImages">
                    <span wire:loading.remove wire:target="saveAndContinue">{{ $step < $total ? 'Enregistrer et continuer' : 'Enregistrer et voir mon profil' }}</span>
                    <span wire:loading wire:target="saveAndContinue">Enregistrement…</span>
                </button>
            </div>
        </form>
    </div>
</div>
