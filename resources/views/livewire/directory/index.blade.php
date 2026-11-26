@use('App\Enums\Availability')
@use('App\Enums\ExperienceLevel')
@use('App\Enums\Profession')
@php
    $profiles = $this->profiles;
    $filterCount = $this->activeFilterCount();
@endphp

<div x-data="{ filtersOpen: false }" x-effect="document.documentElement.classList.toggle('overflow-hidden', filtersOpen)"
     x-on:keydown.escape.window="filtersOpen = false" x-on:livewire:navigating.window="filtersOpen = false">
    <x-page-header title="Annuaire des talents" description="Réservé à l'équipe Bénin Codeurs : trouve un membre par métier, compétence, niveau ou ville pour lui proposer une mission ou une opportunité.">
    <div class="mt-6 flex gap-2">
        <div class="relative flex-1">
            <label for="directory-search" class="sr-only">Rechercher</label>
            @svg('heroicon-m-magnifying-glass', 'pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-muted', ['aria-hidden' => 'true'])
            <input wire:model.live.debounce.400ms="search" id="directory-search" type="search" autocomplete="off"
                   class="field-input border-white py-3 pl-11" placeholder="Nom, compétence, titre, ville…">
        </div>
        <button type="button" x-on:click="filtersOpen = true" aria-controls="directory-filters" x-bind:aria-expanded="filtersOpen.toString()"
                class="btn-secondary shrink-0 border-white lg:hidden">
            @svg('heroicon-m-adjustments-horizontal', 'size-5', ['aria-hidden' => 'true'])
            <span class="hidden sm:inline">Filtres</span>
            @if ($filterCount > 0) <span class="badge bg-lime px-1.5 py-0 text-ink">{{ $filterCount }}</span> @endif
        </button>
    </div>
    </x-page-header>

    <div class="page-container lg:grid lg:grid-cols-[15rem_1fr] lg:items-start lg:gap-10">
        {{-- Filtres : colonne sur ordinateur, panneau coulissant sur mobile et tablette. --}}
        <div x-show="filtersOpen" x-transition.opacity x-cloak x-on:click="filtersOpen = false" class="fixed inset-0 z-40 bg-night/60 lg:hidden" aria-hidden="true"></div>
        <aside id="directory-filters" aria-label="Filtres"
               class="flex flex-col max-lg:invisible max-lg:fixed max-lg:inset-y-0 max-lg:right-0 max-lg:z-50 max-lg:w-[88%] max-lg:max-w-sm max-lg:translate-x-full max-lg:bg-white max-lg:shadow-2xl max-lg:transition-[translate,visibility] max-lg:duration-300 max-lg:motion-reduce:transition-none lg:sticky lg:top-24"
               x-bind:class="{ 'max-lg:invisible': ! filtersOpen, 'max-lg:translate-x-full': ! filtersOpen }">
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-line px-5 lg:hidden">
                <p class="font-semibold text-ink">Filtres</p>
                <button type="button" x-on:click="filtersOpen = false" class="-mr-2 grid size-10 place-items-center rounded-lg text-muted hover:bg-line hover:text-ink">
                    @svg('heroicon-o-x-mark', 'size-6', ['aria-hidden' => 'true'])
                    <span class="sr-only">Fermer les filtres</span>
                </button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto p-5 lg:overflow-visible lg:p-0">
                <div class="hidden items-center justify-between lg:flex">
                    <p class="text-xs font-semibold tracking-wider text-muted uppercase">Filtres</p>
                    @if ($filterCount > 0)
                        <button type="button" wire:click="resetFilters" class="text-xs font-medium text-violet hover:underline">Effacer</button>
                    @endif
                </div>
                <div>
                    <label for="filter-profession" class="field-label">Métier</label>
                    <select wire:model.live="profession" id="filter-profession" class="field-select">
                        <option value="">Tous les métiers</option>
                        @foreach (Profession::options() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-skill" class="field-label">Compétence</label>
                    <select wire:model.live="skill" id="filter-skill" class="field-select">
                        <option value="">Toutes les compétences</option>
                        @foreach ($this->skillCategories as $category)
                            @if ($category->skills->isNotEmpty())
                                <optgroup label="{{ $category->name }}">
                                    @foreach ($category->skills as $skillOption)
                                        <option value="{{ $skillOption->id }}">{{ $skillOption->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-level" class="field-label">Niveau</label>
                    <select wire:model.live="level" id="filter-level" class="field-select">
                        <option value="">Tous les niveaux</option>
                        @foreach (ExperienceLevel::options() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-city" class="field-label">Ville</label>
                    <select wire:model.live="city" id="filter-city" class="field-select">
                        <option value="">Toutes les villes</option>
                        @foreach ($this->cities as $cityName)
                            <option value="{{ $cityName }}">{{ $cityName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-availability" class="field-label">Disponibilité</label>
                    <select wire:model.live="availability" id="filter-availability" class="field-select">
                        <option value="">Toutes</option>
                        @foreach (Availability::options() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex shrink-0 gap-3 border-t border-line p-4 lg:hidden">
                <button type="button" wire:click="resetFilters" class="btn-secondary flex-1" @disabled($filterCount === 0 && $search === '')>Tout effacer</button>
                <button type="button" x-on:click="filtersOpen = false" class="btn-primary flex-1">
                    Voir {{ $profiles->total() }} {{ $profiles->total() > 1 ? 'membres' : 'membre' }}
                </button>
            </div>
        </aside>

        <div class="min-w-0">
            <div class="flex items-center justify-between gap-3" id="resultats">
                <p class="text-sm text-muted" aria-live="polite">
                    <strong class="text-ink">{{ $profiles->total() }}</strong> {{ $profiles->total() > 1 ? 'membres' : 'membre' }}
                </p>
                <div class="flex items-center gap-3">
                    <span wire:loading class="text-sm text-violet">Recherche…</span>
                    @if ($filterCount > 0 || $search !== '')
                        <button type="button" wire:click="resetFilters" class="link hidden text-sm md:inline">Effacer les filtres</button>
                    @endif
                </div>
            </div>

            @if ($profiles->isEmpty())
                <div class="card mt-4 text-center">
                    <div class="mx-auto grid size-14 place-items-center rounded-full bg-paper text-violet" aria-hidden="true">@svg('heroicon-o-magnifying-glass', 'size-7', ['aria-hidden' => 'true'])</div>
                    <h2 class="mt-3 text-lg font-semibold text-ink">Aucun membre ne correspond</h2>
                    <p class="mt-1 text-sm text-muted">
                        @if ($filterCount > 0 || $search !== '')
                            Essaie avec moins de filtres ou un autre mot-clé.
                        @else
                            Les profils apparaissent ici une fois le compte validé et le profil complet à {{ \App\Models\Profile::minCompletion() }}&nbsp;%.
                        @endif
                    </p>
                    @if ($filterCount > 0 || $search !== '')
                        <button type="button" wire:click="resetFilters" class="btn-secondary mt-5">Effacer les filtres</button>
                    @endif
                </div>
            @else
                <div class="mt-4 grid gap-4 transition-opacity sm:grid-cols-2 xl:grid-cols-3 xl:gap-5" wire:loading.class="opacity-60">
                    @foreach ($profiles as $profile)
                        <x-profile-card :profile="$profile" wire:key="profile-{{ $profile->id }}" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $profiles->links('partials.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>
