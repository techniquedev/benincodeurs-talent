<?php

namespace App\Livewire\Directory;

use App\Enums\Availability;
use App\Enums\ExperienceLevel;
use App\Enums\Profession;
use App\Models\Profile;
use App\Models\SkillCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Annuaire des membres : recherche texte, filtres, profils mis en avant en premier. */
#[Layout('layouts.app')]
#[Title('Annuaire')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'metier', except: '')]
    public string $profession = '';

    #[Url(as: 'competence', except: '')]
    public string $skill = '';

    #[Url(as: 'niveau', except: '')]
    public string $level = '';

    #[Url(as: 'ville', except: '')]
    public string $city = '';

    #[Url(as: 'dispo', except: '')]
    public string $availability = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'profession', 'skill', 'level', 'city', 'availability'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'profession', 'skill', 'level', 'city', 'availability');
        $this->resetPage();
    }

    public function activeFilterCount(): int
    {
        return collect([$this->profession, $this->skill, $this->level, $this->city, $this->availability])->filter()->count();
    }

    /** @return Collection<int, SkillCategory> */
    #[Computed]
    public function skillCategories(): Collection
    {
        return SkillCategory::query()->with('skills:id,skill_category_id,name,sort_order')
            ->orderBy('sort_order')->orderBy('name')->get();
    }

    /** @return list<string> Villes des profils visibles. */
    #[Computed]
    public function cities(): array
    {
        return Profile::query()->listed()->whereNotNull('city')
            ->distinct()->orderBy('city')->pluck('city')->all();
    }

    #[Computed]
    public function profiles(): LengthAwarePaginator
    {
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = Profile::query()
            ->listed()
            ->with(['user:id,first_name,last_name', 'skills'])
            ->when($this->profession !== '' && Profession::tryFrom($this->profession), fn (Builder $q) => $q->where('profession', $this->profession))
            ->when($this->level !== '' && ExperienceLevel::tryFrom($this->level), fn (Builder $q) => $q->where('level', $this->level))
            ->when($this->availability !== '' && Availability::tryFrom($this->availability), fn (Builder $q) => $q->where('availability', $this->availability))
            ->when($this->city !== '', fn (Builder $q) => $q->where('city', $this->city))
            ->when(ctype_digit($this->skill), fn (Builder $q) => $q->whereHas('skills', fn (Builder $s) => $s->where('skills.id', (int) $this->skill)));

        // Chaque mot doit apparaître quelque part : nom, titre, présentation, ville, entreprise visible ou compétence.
        foreach (array_slice(preg_split('/\s+/', trim($this->search), -1, PREG_SPLIT_NO_EMPTY), 0, 5) as $word) {
            $term = '%'.addcslashes($word, '%_\\').'%';

            $query->where(fn (Builder $q) => $q
                ->where('headline', $like, $term)
                ->orWhere('bio', $like, $term)
                ->orWhere('city', $like, $term)
                ->orWhere(fn (Builder $c) => $c->where('company_visible', true)->where('company', $like, $term))
                ->orWhereHas('user', fn (Builder $u) => $u->where('first_name', $like, $term)->orWhere('last_name', $like, $term))
                ->orWhereHas('skills', fn (Builder $s) => $s->where('skills.name', $like, $term)));
        }

        return $query
            ->orderByDesc('is_featured')
            ->orderByDesc('featured_at')
            ->orderByDesc('completion')
            ->orderByDesc('updated_at')
            ->paginate(config('talent.directory_per_page', 12));
    }

    public function render(): View
    {
        return view('livewire.directory.index');
    }
}
