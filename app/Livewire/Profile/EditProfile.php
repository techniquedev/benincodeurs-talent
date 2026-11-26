<?php

namespace App\Livewire\Profile;

use App\Enums\Availability;
use App\Enums\ExperienceLevel;
use App\Enums\Profession;
use App\Enums\SkillLevel;
use App\Enums\WorkStatus;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Support\Media;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Formulaire de profil en 6 étapes. Chaque passage d'une étape à l'autre enregistre
 * l'étape en cours et recalcule la jauge de complétion.
 */
#[Layout('layouts.app')]
#[Title('Mon profil')]
class EditProfile extends Component
{
    use WithFileUploads;

    private const SUGGESTION_COUNT = 8;

    private const IMAGE_RULES = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

    #[Url(as: 'etape')]
    public int $step = 1;

    // 1. Identité
    /** @var TemporaryUploadedFile|null */
    public $photo = null;

    public string $city = '';

    public string $headline = '';

    public string $bio = '';

    // 2. Métier et niveau
    public string $profession = '';

    public string $level = '';

    public string $years_experience = '';

    // 3. Compétences : liste de ['id' => int, 'level' => string, 'highlighted' => bool]
    /** @var list<array{id: int, level: string, highlighted: bool}> */
    public array $skills = [];

    // 4. Situation
    public string $work_status = '';

    public string $company = '';

    public string $availability = '';

    // 5. Réalisations : liste de ['key', 'id', 'title', 'description', 'url', 'image_path']
    /** @var list<array{key: string, id: ?int, title: string, description: string, url: string, image_path: ?string}> */
    public array $projects = [];

    /** @var array<string, TemporaryUploadedFile> Images envoyées, par clé de réalisation. */
    public array $projectImages = [];

    // 6. Liens et contact
    public string $linkedin_url = '';

    public string $github_url = '';

    public string $portfolio_url = '';

    public string $behance_url = '';

    public function mount(): void
    {
        $this->step = max(1, min(count(Profile::STEPS), $this->step));
        $this->fillFromProfile();
    }

    #[Computed]
    public function profile(): Profile
    {
        return auth()->user()->ensureProfile();
    }

    /** @return Collection<int, SkillCategory> */
    #[Computed]
    public function categories(): Collection
    {
        return SkillCategory::query()->with('skills')->orderBy('sort_order')->orderBy('name')->get()
            ->filter(fn (SkillCategory $category) => $category->skills->isNotEmpty())->values();
    }

    /**
     * Toutes les compétences, pour la recherche avec suggestions (filtrée côté navigateur).
     *
     * @return list<array{id: int, name: string, category: string}>
     */
    #[Computed]
    public function skillCatalog(): array
    {
        return $this->categories
            ->flatMap(fn (SkillCategory $category) => $category->skills->map(fn (Skill $skill) => [
                'id' => $skill->id,
                'name' => $skill->name,
                'category' => $category->name,
            ]))
            ->values()
            ->all();
    }

    /**
     * 8 compétences suggérées selon le métier choisi (étape 2), sinon les plus choisies par les membres.
     *
     * @return Collection<int, Skill>
     */
    #[Computed]
    public function suggestedSkills(): Collection
    {
        $allSkills = $this->categories->flatMap->skills;
        $profession = Profession::tryFrom($this->profession);

        $suggested = $profession
            ? collect($profession->suggestedSkillNames())
                ->map(fn (string $name) => $allSkills->first(fn (Skill $skill) => mb_strtolower($skill->name) === mb_strtolower($name)))
                ->filter()
            : collect();

        if ($suggested->count() < self::SUGGESTION_COUNT) {
            $popularIds = Skill::query()->withCount('profiles')->orderByDesc('profiles_count')->orderBy('name')
                ->limit(self::SUGGESTION_COUNT * 2)->pluck('id');
            $suggested = $suggested->concat($popularIds->map(fn (int $id) => $allSkills->firstWhere('id', $id))->filter());
        }

        return $suggested->unique('id')->take(self::SUGGESTION_COUNT)->values();
    }

    /** @return list<array{label: string, step: int, weight: int, done: bool}> */
    #[Computed]
    public function checklist(): array
    {
        return $this->profile->completionChecklist();
    }

    /** Enregistre l'étape en cours puis passe à la suivante (ou termine). */
    public function saveAndContinue(): void
    {
        $this->saveCurrentStep();

        if ($this->step < count(Profile::STEPS)) {
            $this->step++;
            $this->dispatch('profile-step-changed');

            return;
        }

        session()->flash('status', "Ton profil est enregistré. Voici ton profil tel que le voit l'équipe Bénin Codeurs.");
        $this->redirectRoute('profile.preview');
    }

    /** Enregistre l'étape en cours puis va à l'étape demandée. */
    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > count(Profile::STEPS) || $step === $this->step) {
            return;
        }

        $this->saveCurrentStep();
        $this->step = $step;
        $this->dispatch('profile-step-changed');
    }

    private function saveCurrentStep(): void
    {
        match ($this->step) {
            1 => $this->saveIdentity(),
            2 => $this->saveProfession(),
            3 => $this->saveSkills(),
            4 => $this->saveSituation(),
            5 => $this->saveProjects(),
            6 => $this->saveLinks(),
        };

        $this->profile->refreshCompletion();
        unset($this->checklist);
    }

    private function saveIdentity(): void
    {
        $this->city = Str::squish($this->city);
        $this->headline = Str::squish($this->headline);
        $this->bio = trim($this->bio);

        $data = $this->validate([
            'photo' => self::IMAGE_RULES,
            'city' => ['nullable', 'string', 'max:100'],
            'headline' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:'.Profile::BIO_MAX_LENGTH],
        ], attributes: ['photo' => 'photo', 'city' => 'ville', 'headline' => 'titre professionnel', 'bio' => 'présentation']);

        $profile = $this->profile;

        if ($this->photo) {
            $old = $profile->photo_path;
            $profile->photo_path = Media::store($this->photo, 'photos');
            Media::delete($old);
            $this->photo = null;
        }

        $profile->fill([
            'city' => $data['city'] !== '' ? mb_convert_case($data['city'], MB_CASE_TITLE) : null,
            'headline' => $data['headline'] ?: null,
            'bio' => $data['bio'] ?: null,
        ])->save();

        $this->city = (string) $profile->city;
    }

    /** Vérifie la photo dès l'envoi, pour afficher l'erreur sans attendre l'enregistrement. */
    public function updatedPhoto(): void
    {
        $this->validateOnly('photo', ['photo' => self::IMAGE_RULES], [], ['photo' => 'photo']);
    }

    public function updatedProjectImages(mixed $value, string $key): void
    {
        $this->validateOnly("projectImages.$key", ["projectImages.$key" => self::IMAGE_RULES], [], ["projectImages.$key" => 'image']);
    }

    public function removePhoto(): void
    {
        $this->photo = null;
        $profile = $this->profile;

        if ($profile->photo_path) {
            Media::delete($profile->photo_path);
            $profile->forceFill(['photo_path' => null])->save();
            $profile->refreshCompletion();
            unset($this->checklist);
        }
    }

    private function saveProfession(): void
    {
        $data = $this->validate([
            'profession' => ['nullable', Rule::enum(Profession::class)],
            'level' => ['nullable', Rule::enum(ExperienceLevel::class)],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
        ], attributes: ['profession' => 'métier', 'level' => 'niveau', 'years_experience' => "années d'expérience"]);

        $this->profile->fill([
            'profession' => $data['profession'] ?: null,
            'level' => $data['level'] ?: null,
            'years_experience' => $data['years_experience'] === '' || $data['years_experience'] === null ? null : (int) $data['years_experience'],
        ])->save();
    }

    public function addSkill(int $skillId): void
    {
        if ($this->skillIndex($skillId) !== null || ! $this->categories->flatMap->skills->contains('id', $skillId)) {
            return;
        }

        if (count($this->skills) >= Profile::MAX_SKILLS) {
            $this->addError('skills', 'Tu peux choisir '.Profile::MAX_SKILLS.' compétences au maximum. Retire-en une pour en ajouter une autre.');

            return;
        }

        $this->skills[] = ['id' => $skillId, 'level' => SkillLevel::Intermediate->value, 'highlighted' => false];
        $this->resetErrorBag('skills');
    }

    public function removeSkill(int $skillId): void
    {
        $index = $this->skillIndex($skillId);

        if ($index === null) {
            return;
        }

        unset($this->skills[$index]);
        $this->skills = array_values($this->skills);
        $this->resetErrorBag('skills');
    }

    /** Ajoute la compétence si elle n'est pas choisie, la retire sinon (puces « Parcourir par catégorie »). */
    public function toggleSkill(int $skillId): void
    {
        if ($this->skillIndex($skillId) !== null) {
            $this->removeSkill($skillId);

            return;
        }

        $this->addSkill($skillId);
    }

    public function setSkillLevel(int $skillId, string $level): void
    {
        $index = $this->skillIndex($skillId);

        if ($index === null || SkillLevel::tryFrom($level) === null) {
            return;
        }

        $this->skills[$index]['level'] = $level;
    }

    public function toggleHighlight(int $skillId): void
    {
        $index = $this->skillIndex($skillId);

        if ($index === null) {
            return;
        }

        if (! $this->skills[$index]['highlighted'] && collect($this->skills)->where('highlighted', true)->count() >= Profile::MAX_HIGHLIGHTED_SKILLS) {
            $this->addError('skills', 'Tu peux mettre en avant '.Profile::MAX_HIGHLIGHTED_SKILLS.' compétences au maximum.');

            return;
        }

        $this->skills[$index]['highlighted'] = ! $this->skills[$index]['highlighted'];
        $this->resetErrorBag('skills');
    }

    private function skillIndex(int $skillId): ?int
    {
        foreach ($this->skills as $index => $skill) {
            if ((int) $skill['id'] === $skillId) {
                return $index;
            }
        }

        return null;
    }

    private function saveSkills(): void
    {
        $this->validate([
            'skills' => ['array', 'max:'.Profile::MAX_SKILLS],
            'skills.*.id' => ['required', 'integer', 'distinct', Rule::exists('skills', 'id')],
            'skills.*.level' => ['required', Rule::enum(SkillLevel::class)],
            'skills.*.highlighted' => ['boolean'],
        ], [
            'skills.max' => 'Tu peux choisir :max compétences au maximum.',
        ], ['skills.*.level' => 'niveau']);

        if (collect($this->skills)->where('highlighted', true)->count() > Profile::MAX_HIGHLIGHTED_SKILLS) {
            throw ValidationException::withMessages(['skills' => 'Tu peux mettre en avant '.Profile::MAX_HIGHLIGHTED_SKILLS.' compétences au maximum.']);
        }

        $this->profile->skills()->sync(collect($this->skills)->mapWithKeys(fn (array $skill) => [
            (int) $skill['id'] => ['level' => $skill['level'], 'is_highlighted' => (bool) $skill['highlighted']],
        ])->all());
    }

    private function saveSituation(): void
    {
        $this->company = Str::squish($this->company);

        $data = $this->validate([
            'work_status' => ['nullable', Rule::enum(WorkStatus::class)],
            'company' => ['nullable', 'string', 'max:150'],
            'availability' => ['nullable', Rule::enum(Availability::class)],
        ], attributes: ['work_status' => 'situation', 'company' => 'entreprise', 'availability' => 'disponibilité']);

        $this->profile->fill([
            'work_status' => $data['work_status'] ?: null,
            'company' => $data['company'] ?: null,
            'availability' => $data['availability'] ?: null,
        ])->save();
    }

    public function addProject(): void
    {
        if (count($this->projects) >= Profile::MAX_PROJECTS) {
            return;
        }

        $this->projects[] = $this->blankProject();
    }

    public function removeProject(int $index): void
    {
        $key = $this->projects[$index]['key'] ?? null;
        unset($this->projects[$index], $this->projectImages[$key]);
        $this->projects = array_values($this->projects);
        $this->resetValidation();
    }

    public function removeProjectImage(int $index): void
    {
        if (! isset($this->projects[$index])) {
            return;
        }

        unset($this->projectImages[$this->projects[$index]['key']]);
        $this->projects[$index]['image_path'] = null;
    }

    private function saveProjects(): void
    {
        foreach ($this->projects as $index => $project) {
            $this->projects[$index]['title'] = Str::squish($project['title']);
            $this->projects[$index]['description'] = trim($project['description']);
            $this->projects[$index]['url'] = $this->normalizeUrl($project['url']);
        }

        $this->validate([
            'projects' => ['array', 'max:'.Profile::MAX_PROJECTS],
            'projects.*.title' => ['required', 'string', 'max:120'],
            'projects.*.description' => ['nullable', 'string', 'max:500'],
            'projects.*.url' => ['nullable', 'url:http,https', 'max:255'],
            'projectImages.*' => self::IMAGE_RULES,
        ], [
            'projects.*.title.required' => 'Donne un titre à cette réalisation (ou retire-la).',
        ], [
            'projects.*.title' => 'titre',
            'projects.*.description' => 'description',
            'projects.*.url' => 'lien',
            'projectImages.*' => 'image',
        ]);

        $profile = $this->profile;
        $existing = $profile->projects()->get()->keyBy('id');
        $keptIds = [];

        foreach ($this->projects as $index => $data) {
            /** @var Project $project */
            $project = ($data['id'] ? $existing->get($data['id']) : null) ?? $profile->projects()->make();
            $oldImage = $project->image_path;
            $upload = $this->projectImages[$data['key']] ?? null;

            $imagePath = $upload ? Media::store($upload, 'projects') : $data['image_path'];

            $project->fill([
                'title' => $data['title'],
                'description' => $data['description'] ?: null,
                'url' => $data['url'] ?: null,
                'image_path' => $imagePath,
                'sort_order' => $index,
            ])->save();

            if ($oldImage && $oldImage !== $imagePath) {
                Media::delete($oldImage);
            }

            $keptIds[] = $project->id;
            $this->projects[$index]['id'] = $project->id;
            $this->projects[$index]['image_path'] = $imagePath;
        }

        $existing->except($keptIds)->each(function (Project $project) {
            Media::delete($project->image_path);
            $project->delete();
        });

        $this->projectImages = [];
    }

    private function saveLinks(): void
    {
        foreach (['linkedin_url', 'github_url', 'portfolio_url', 'behance_url'] as $field) {
            $this->{$field} = $this->normalizeUrl($this->{$field});
        }

        $url = ['nullable', 'url:http,https', 'max:255'];

        $data = $this->validate([
            'linkedin_url' => $url,
            'github_url' => $url,
            'portfolio_url' => $url,
            'behance_url' => $url,
        ], attributes: [
            'linkedin_url' => 'lien LinkedIn',
            'github_url' => 'lien GitHub',
            'portfolio_url' => 'lien du portfolio',
            'behance_url' => 'lien Behance',
        ]);

        $this->profile->fill([
            'linkedin_url' => $data['linkedin_url'] ?: null,
            'github_url' => $data['github_url'] ?: null,
            'portfolio_url' => $data['portfolio_url'] ?: null,
            'behance_url' => $data['behance_url'] ?: null,
        ])->save();
    }

    /** Ajoute « https:// » aux liens saisis sans protocole (ex. « github.com/moi »). */
    private function normalizeUrl(string $url): string
    {
        $url = trim($url);

        if ($url !== '' && ! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }

        return $url;
    }

    /** @return array{key: string, id: ?int, title: string, description: string, url: string, image_path: ?string} */
    private function blankProject(): array
    {
        return ['key' => 'p'.Str::lower(Str::random(8)), 'id' => null, 'title' => '', 'description' => '', 'url' => '', 'image_path' => null];
    }

    private function fillFromProfile(): void
    {
        $profile = $this->profile;

        $this->city = (string) $profile->city;
        $this->headline = (string) $profile->headline;
        $this->bio = (string) $profile->bio;

        $this->profession = (string) $profile->profession?->value;
        $this->level = (string) $profile->level?->value;
        $this->years_experience = $profile->years_experience === null ? '' : (string) $profile->years_experience;

        $this->skills = $profile->skills->map(fn ($skill) => [
            'id' => $skill->id,
            'level' => $skill->pivot->level,
            'highlighted' => (bool) $skill->pivot->is_highlighted,
        ])->values()->all();

        $this->work_status = (string) $profile->work_status?->value;
        $this->company = (string) $profile->company;
        $this->availability = (string) $profile->availability?->value;

        $this->projects = $profile->projects->map(fn (Project $project) => [
            'key' => 'p'.$project->id,
            'id' => $project->id,
            'title' => $project->title,
            'description' => (string) $project->description,
            'url' => (string) $project->url,
            'image_path' => $project->image_path,
        ])->values()->all();

        $this->linkedin_url = (string) $profile->linkedin_url;
        $this->github_url = (string) $profile->github_url;
        $this->portfolio_url = (string) $profile->portfolio_url;
        $this->behance_url = (string) $profile->behance_url;
    }

    public function render(): View
    {
        return view('livewire.profile.edit-profile', [
            'steps' => Profile::STEPS,
            'completion' => $this->profile->completion,
            'minCompletion' => Profile::minCompletion(),
        ]);
    }
}
