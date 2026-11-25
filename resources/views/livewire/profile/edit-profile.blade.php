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
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-violet">Étape {{ $step }} sur {{ $total }}</p>
            <h1 class="text-2xl font-extrabold tracking-tight text-brand sm:text-3xl">Mon profil</h1>
        </div>
        <a href="{{ route('profile.preview') }}" class="btn-secondary text-sm">Voir mon profil</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[17rem_1fr] lg:items-start">
        {{-- Colonne latérale : jauge, étapes, éléments manquants --}}
        <aside class="space-y-4 lg:sticky lg:top-6">
            <div class="card flex items-center gap-4 p-5 sm:p-5 lg:flex-col lg:text-center">
                <x-completion-ring :value="$completion" :threshold="$minCompletion" class="size-20 lg:size-28" />
                <div>
                    <p class="font-bold text-brand">Profil complet à {{ $completion }}&nbsp;%</p>
                    @if ($completion >= $minCompletion)
                        <p class="mt-1 text-sm text-gray-600">
                            @if ($user->isApproved())
                                Ton profil apparaît dans l'annuaire.
                            @else
                                Ton profil apparaîtra dans l'annuaire dès que ton compte sera validé.
                            @endif
                        </p>
                    @else
                        <p class="mt-1 text-sm text-gray-600">Encore {{ $minCompletion - $completion }}&nbsp;% pour apparaître dans l'annuaire ({{ $minCompletion }}&nbsp;% minimum).</p>
                    @endif
                </div>
            </div>

            {{-- Étapes : onglets défilants sur mobile, liste sur grand écran --}}
            <nav aria-label="Étapes du profil" class="-mx-4 overflow-x-auto px-4 lg:mx-0 lg:overflow-visible lg:px-0">
                <ol class="flex gap-2 lg:flex-col lg:gap-1 lg:rounded-card lg:bg-white lg:p-3 lg:shadow-xl lg:shadow-night/10">
                    @foreach ($steps as $number => $label)
                        <li class="shrink-0">
                            <button type="button" wire:click="goToStep({{ $number }})" @if ($number === $step) aria-current="step" @endif
                                    @class([
                                        'flex w-full items-center gap-2.5 rounded-full px-3 py-2 text-left text-sm font-semibold transition lg:rounded-xl',
                                        'bg-brand text-white' => $number === $step,
                                        'bg-white text-brand hover:bg-brand/5 lg:bg-transparent' => $number !== $step,
                                    ])>
                                <span @class([
                                    'grid size-6 shrink-0 place-items-center rounded-full text-xs font-bold',
                                    'bg-lime text-ink' => $stepDone($number),
                                    'bg-white/20 text-white' => ! $stepDone($number) && $number === $step,
                                    'bg-brand/10 text-brand' => ! $stepDone($number) && $number !== $step,
                                ])>
                                    @if ($stepDone($number)) ✓ @else {{ $number }} @endif
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
                    <p class="text-sm font-bold text-brand">Pour compléter ton profil</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($missing as $item)
                            <li>
                                <button type="button" wire:click="goToStep({{ $item['step'] }})" class="flex w-full items-center justify-between gap-2 text-left text-gray-700 hover:text-violet">
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
        <form wire:submit="saveAndContinue" class="card" novalidate>
            <h2 class="text-xl font-extrabold tracking-tight text-brand">{{ $steps[$step] }}</h2>

            {{-- 1. Identité --}}
            @if ($step === 1)
                <p class="mt-1 text-sm text-gray-600">Ce que les autres membres voient en premier.</p>

                <div class="mt-6 space-y-5">
                    <div>
                        <span class="field-label">Photo</span>
                        <div class="flex items-center gap-4">
                            @if ($photo && method_exists($photo, 'isPreviewable') && $photo->isPreviewable())
                                <img src="{{ $photo->temporaryUrl() }}" alt="Aperçu de la nouvelle photo" class="size-20 rounded-full object-cover sm:size-24">
                            @else
                                <x-avatar :profile="$this->profile" :user="$user" size="size-20 sm:size-24" text="text-2xl" :pixels="200" />
                            @endif
                            <div class="flex flex-col gap-2">
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
                <p class="mt-1 text-sm text-gray-600">Ton métier principal et où tu en es aujourd'hui.</p>

                <div class="mt-6 space-y-6">
                    <fieldset>
                        <legend class="field-label">Métier principal</legend>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach (Profession::cases() as $option)
                                <label @class([
                                    'flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold transition',
                                    'border-violet bg-violet/5 text-brand ring-2 ring-violet/20' => $profession === $option->value,
                                    'border-line text-gray-700 hover:border-violet/40' => $profession !== $option->value,
                                ])>
                                    <input type="radio" wire:model.live="profession" value="{{ $option->value }}" class="size-4 accent-violet">
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
                                    'flex cursor-pointer items-start gap-3 rounded-xl border px-4 py-3 transition',
                                    'border-violet bg-violet/5 ring-2 ring-violet/20' => $level === $option->value,
                                    'border-line hover:border-violet/40' => $level !== $option->value,
                                ])>
                                    <input type="radio" wire:model.live="level" value="{{ $option->value }}" class="mt-1 size-4 accent-violet">
                                    <span>
                                        <span class="block text-sm font-bold text-brand">{{ $option->label() }}</span>
                                        <span class="block text-sm text-gray-600">{{ $option->description() }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('level') <p class="field-error">{{ $message }}</p> @enderror
                    </fieldset>

                    <div class="max-w-xs">
                        <label for="years_experience" class="field-label">Années d'expérience</label>
                        <input wire:model="years_experience" id="years_experience" type="number" min="0" max="50" inputmode="numeric" class="field-input" placeholder="Ex. 3">
                        <p class="field-help">Mets 0 si tu débutes.</p>
                        @error('years_experience') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            {{-- 3. Compétences --}}
            @if ($step === 3)
                @php
                    $skillNames = $this->categories->flatMap->skills->pluck('name', 'id');
                    $selectedIds = collect($skills)->pluck('id')->map(fn ($id) => (int) $id)->all();
                    $highlightedCount = collect($skills)->where('highlighted', true)->count();
                @endphp
                <p class="mt-1 text-sm text-gray-600">
                    Choisis jusqu'à {{ Profile::MAX_SKILLS }} compétences et indique ton niveau.
                    Mets-en {{ Profile::MAX_HIGHLIGHTED_SKILLS }} en avant : elles s'affichent sur ta carte dans l'annuaire.
                </p>

                @error('skills') <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</div> @enderror

                <div class="mt-6">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold text-brand">Mes compétences</h3>
                        <p class="text-xs font-semibold text-gray-500">{{ count($skills) }}/{{ Profile::MAX_SKILLS }} · {{ $highlightedCount }}/{{ Profile::MAX_HIGHLIGHTED_SKILLS }} en avant</p>
                    </div>

                    @if ($skills === [])
                        <p class="mt-3 rounded-xl border border-dashed border-line px-4 py-6 text-center text-sm text-gray-500">Aucune compétence choisie. Touche une compétence ci-dessous pour l'ajouter.</p>
                    @else
                        <ul class="mt-3 divide-y divide-line rounded-xl border border-line">
                            @foreach ($skills as $i => $skill)
                                <li wire:key="selected-{{ $skill['id'] }}" class="flex flex-wrap items-center gap-x-3 gap-y-2 px-3 py-3 sm:flex-nowrap">
                                    <button type="button" wire:click="toggleHighlight({{ $skill['id'] }})"
                                            aria-pressed="{{ $skill['highlighted'] ? 'true' : 'false' }}"
                                            title="{{ $skill['highlighted'] ? 'Retirer de la mise en avant' : 'Mettre en avant' }}"
                                            @class([
                                                'grid size-9 shrink-0 place-items-center rounded-full text-lg transition',
                                                'bg-lime text-ink' => $skill['highlighted'],
                                                'bg-gray-100 text-gray-400 hover:text-violet' => ! $skill['highlighted'],
                                            ])>
                                        <span aria-hidden="true">★</span>
                                        <span class="sr-only">{{ $skill['highlighted'] ? 'Mise en avant' : 'Mettre en avant' }}</span>
                                    </button>
                                    <span class="min-w-0 flex-1 font-semibold text-brand">{{ $skillNames[$skill['id']] ?? 'Compétence retirée' }}</span>
                                    <label class="sr-only" for="skill-level-{{ $skill['id'] }}">Niveau en {{ $skillNames[$skill['id']] ?? '' }}</label>
                                    <select wire:model="skills.{{ $i }}.level" id="skill-level-{{ $skill['id'] }}" class="field-select w-auto min-w-36 py-2 text-base sm:text-sm">
                                        @foreach (SkillLevel::cases() as $option)
                                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="toggleSkill({{ $skill['id'] }})" class="grid size-9 shrink-0 place-items-center rounded-full text-gray-400 hover:bg-red-50 hover:text-red-700" title="Retirer">
                                        <span aria-hidden="true">✕</span><span class="sr-only">Retirer</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="mt-8" x-data="{ q: '' }">
                    <label for="skill-search" class="field-label">Ajouter des compétences</label>
                    <input id="skill-search" type="search" x-model="q" class="field-input" placeholder="Rechercher une compétence (ex. Laravel, Figma…)" autocomplete="off">

                    <div class="mt-5 space-y-5">
                        @foreach ($this->categories as $category)
                            <section wire:key="category-{{ $category->id }}"
                                     data-names="{{ mb_strtolower($category->skills->pluck('name')->join('|')) }}"
                                     x-show="q === '' || $el.dataset.names.includes(q.toLowerCase())">
                                <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ $category->name }}</h3>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($category->skills as $skillOption)
                                        @php $isSelected = in_array($skillOption->id, $selectedIds, true); @endphp
                                        <button type="button" wire:key="skill-{{ $skillOption->id }}" wire:click="toggleSkill({{ $skillOption->id }})"
                                                aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                                                data-name="{{ mb_strtolower($skillOption->name) }}"
                                                x-show="q === '' || $el.dataset.name.includes(q.toLowerCase())"
                                                @class([
                                                    'chip',
                                                    'border-brand bg-brand text-white' => $isSelected,
                                                    'border-line bg-white text-gray-700 hover:border-violet hover:text-violet' => ! $isSelected,
                                                ])>
                                            @if ($isSelected) <span aria-hidden="true">✓</span> @else <span aria-hidden="true">+</span> @endif
                                            {{ $skillOption->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 4. Situation --}}
            @if ($step === 4)
                <p class="mt-1 text-sm text-gray-600">Ta situation actuelle et ce que tu recherches.</p>

                <div class="mt-6 space-y-6">
                    <fieldset>
                        <legend class="field-label">Situation</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach (WorkStatus::cases() as $option)
                                <label @class([
                                    'chip cursor-pointer px-4 py-2',
                                    'border-brand bg-brand text-white' => $work_status === $option->value,
                                    'border-line bg-white text-gray-700 hover:border-violet' => $work_status !== $option->value,
                                ])>
                                    <input type="radio" wire:model.live="work_status" value="{{ $option->value }}" class="sr-only">
                                    {{ $option->label() }}
                                </label>
                            @endforeach
                        </div>
                        @error('work_status') <p class="field-error">{{ $message }}</p> @enderror
                    </fieldset>

                    <div>
                        <label for="company" class="field-label">Entreprise actuelle <span class="font-normal text-gray-500">(facultatif)</span></label>
                        <input wire:model="company" id="company" type="text" maxlength="150" class="field-input" placeholder="Ex. Bénin Codeurs">
                        @error('company') <p class="field-error">{{ $message }}</p> @enderror
                        <label class="mt-3 flex cursor-pointer items-center gap-3 text-sm text-gray-700">
                            <input type="checkbox" wire:model="company_visible" class="size-5 rounded accent-violet">
                            Afficher mon entreprise aux autres membres
                        </label>
                    </div>

                    <fieldset>
                        <legend class="field-label">Disponibilité</legend>
                        <div class="grid gap-2 sm:grid-cols-3">
                            @foreach (Availability::cases() as $option)
                                <label @class([
                                    'flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold transition',
                                    'border-violet bg-violet/5 text-brand ring-2 ring-violet/20' => $availability === $option->value,
                                    'border-line text-gray-700 hover:border-violet/40' => $availability !== $option->value,
                                ])>
                                    <input type="radio" wire:model.live="availability" value="{{ $option->value }}" class="size-4 accent-violet">
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
                <p class="mt-1 text-sm text-gray-600">Jusqu'à {{ Profile::MAX_PROJECTS }} projets dont tu es fier : site, application, maquette, campagne, étude…</p>

                <div class="mt-6 space-y-5">
                    @forelse ($projects as $i => $project)
                        @php $upload = $projectImages[$project['key']] ?? null; @endphp
                        <fieldset wire:key="project-{{ $project['key'] }}" class="rounded-2xl border border-line p-4 sm:p-5">
                            <div class="flex items-center justify-between gap-2">
                                <legend class="text-sm font-bold text-brand">Réalisation {{ $i + 1 }}</legend>
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
                                            <img src="{{ $upload->temporaryUrl() }}" alt="" class="h-20 w-32 rounded-xl object-cover">
                                        @elseif ($project['image_path'])
                                            <img src="{{ \App\Support\Media::url($project['image_path'], 320, 200) }}" alt="" class="h-20 w-32 rounded-xl object-cover">
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
                        <p class="rounded-xl border border-dashed border-line px-4 py-6 text-center text-sm text-gray-500">Aucune réalisation pour l'instant.</p>
                    @endforelse

                    @if (count($projects) < Profile::MAX_PROJECTS)
                        <button type="button" wire:click="addProject" class="btn-secondary w-full border-dashed">+ Ajouter une réalisation</button>
                    @endif
                </div>
            @endif

            {{-- 6. Liens et contact --}}
            @if ($step === 6)
                <p class="mt-1 text-sm text-gray-600">Où les autres membres peuvent voir ton travail et te contacter.</p>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    @foreach (['linkedin_url' => ['LinkedIn', 'linkedin.com/in/ton-nom'], 'github_url' => ['GitHub', 'github.com/ton-pseudo'], 'portfolio_url' => ['Portfolio', 'ton-site.com'], 'behance_url' => ['Behance', 'behance.net/ton-pseudo']] as $field => [$label, $placeholder])
                        <div>
                            <label for="{{ $field }}" class="field-label">{{ $label }}</label>
                            <input wire:model="{{ $field }}" id="{{ $field }}" type="text" inputmode="url" autocomplete="url" class="field-input" placeholder="{{ $placeholder }}">
                            @error($field) <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>

                <fieldset class="mt-8">
                    <legend class="field-label">Contact visible par les autres membres</legend>
                    <div class="space-y-3">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-line px-4 py-3">
                            <input type="checkbox" wire:model="whatsapp_visible" class="mt-0.5 size-5 rounded accent-violet">
                            <span>
                                <span class="block text-sm font-bold text-brand">Afficher mon WhatsApp</span>
                                <span class="block text-sm text-gray-600">{{ $user->whatsapp ?: 'Aucun numéro enregistré' }}</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-line px-4 py-3">
                            <input type="checkbox" wire:model="email_visible" class="mt-0.5 size-5 rounded accent-violet">
                            <span>
                                <span class="block text-sm font-bold text-brand">Afficher mon e-mail</span>
                                <span class="block text-sm break-all text-gray-600">{{ $user->email }}</span>
                            </span>
                        </label>
                    </div>
                    <p class="field-help">Masqués par défaut. Seuls les membres validés de la communauté voient l'annuaire.</p>
                </fieldset>
            @endif

            {{-- Navigation entre étapes --}}
            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                @if ($step > 1)
                    <button type="button" wire:click="goToStep({{ $step - 1 }})" class="btn-secondary">← Précédent</button>
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
