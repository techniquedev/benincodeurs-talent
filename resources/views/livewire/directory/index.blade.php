@use('App\Enums\Availability')
@use('App\Enums\ExperienceLevel')
@use('App\Enums\Profession')
@php
    $profiles = $this->profiles;
    $filterCount = $this->activeFilterCount();
@endphp

<div>
    <div class="relative overflow-hidden rounded-card bg-brand px-5 py-7 text-white sm:px-8 sm:py-9">
        <div aria-hidden="true" class="pointer-events-none absolute -top-20 -right-16 size-64 rounded-full bg-violet/50 blur-3xl"></div>
        <div class="relative">
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Annuaire des talents</h1>
            <p class="mt-1 max-w-2xl text-sm text-white/80 sm:text-base">Trouve un membre de la communauté VIP Bénin Codeurs par métier, compétence, niveau ou ville.</p>

            <div class="mt-5">
                <label for="directory-search" class="sr-only">Rechercher</label>
                <div class="relative">
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="currentColor" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-gray-400"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.45 4.39l3.08 3.08a.75.75 0 1 1-1.06 1.06l-3.08-3.08A7 7 0 0 1 2 9Z" clip-rule="evenodd"/></svg>
                    <input wire:model.live.debounce.400ms="search" id="directory-search" type="search" autocomplete="off"
                           class="field-input border-0 py-3.5 pl-12 shadow-lg" placeholder="Nom, compétence, titre, ville…">
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres : repliés sur mobile, toujours visibles sur grand écran --}}
    <div x-data="{ open: false }" class="mt-4">
        <div class="flex items-center justify-between gap-3 md:hidden">
            <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()" aria-controls="directory-filters" class="btn-secondary bg-white text-sm">
                Filtres @if ($filterCount > 0) <span class="badge bg-lime px-2 py-0.5 text-ink">{{ $filterCount }}</span> @endif
                <span aria-hidden="true" x-text="open ? '▲' : '▼'" class="text-xs">▼</span>
            </button>
            @if ($filterCount > 0 || $search !== '')
                <button type="button" wire:click="resetFilters" class="link text-sm">Tout effacer</button>
            @endif
        </div>

        <div id="directory-filters" x-cloak x-bind:class="open ? 'grid' : 'hidden'"
             class="mt-3 grid-cols-1 gap-3 rounded-card bg-white p-4 ring-1 ring-line sm:grid-cols-2 md:mt-0 md:grid md:grid-cols-3 lg:grid-cols-5">
            <div>
                <label for="filter-profession" class="field-label text-xs">Métier</label>
                <select wire:model.live="profession" id="filter-profession" class="field-select py-2.5 text-base md:text-sm">
                    <option value="">Tous les métiers</option>
                    @foreach (Profession::options() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-skill" class="field-label text-xs">Compétence</label>
                <select wire:model.live="skill" id="filter-skill" class="field-select py-2.5 text-base md:text-sm">
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
                <label for="filter-level" class="field-label text-xs">Niveau</label>
                <select wire:model.live="level" id="filter-level" class="field-select py-2.5 text-base md:text-sm">
                    <option value="">Tous les niveaux</option>
                    @foreach (ExperienceLevel::options() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-city" class="field-label text-xs">Ville</label>
                <select wire:model.live="city" id="filter-city" class="field-select py-2.5 text-base md:text-sm">
                    <option value="">Toutes les villes</option>
                    @foreach ($this->cities as $cityName)
                        <option value="{{ $cityName }}">{{ $cityName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-availability" class="field-label text-xs">Disponibilité</label>
                <select wire:model.live="availability" id="filter-availability" class="field-select py-2.5 text-base md:text-sm">
                    <option value="">Toutes</option>
                    @foreach (Availability::options() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="mt-6 flex items-center justify-between gap-3" id="resultats">
        <p class="text-sm text-gray-600" aria-live="polite">
            <strong class="text-brand">{{ $profiles->total() }}</strong> {{ $profiles->total() > 1 ? 'membres' : 'membre' }}
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
            <div class="mx-auto grid size-14 place-items-center rounded-full bg-paper text-2xl" aria-hidden="true">🔍</div>
            <h2 class="mt-3 text-lg font-bold text-brand">Aucun membre ne correspond</h2>
            <p class="mt-1 text-sm text-gray-600">
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
        <div class="mt-4 grid gap-4 transition-opacity sm:grid-cols-2 lg:grid-cols-3" wire:loading.class="opacity-60">
            @foreach ($profiles as $profile)
                <x-profile-card :profile="$profile" wire:key="profile-{{ $profile->id }}" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $profiles->links('partials.pagination') }}
        </div>
    @endif
</div>
