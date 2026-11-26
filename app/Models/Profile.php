<?php

namespace App\Models;

use App\Enums\Availability;
use App\Enums\ExperienceLevel;
use App\Enums\Profession;
use App\Enums\UserStatus;
use App\Enums\WorkStatus;
use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Profil d'un membre (un par compte). Rempli en 6 étapes ; vu uniquement par l'équipe Bénin Codeurs.
 * Il entre dans l'annuaire de l'équipe quand le compte est validé et que la complétion atteint
 * config('talent.directory_min_completion').
 */
class Profile extends Model
{
    public const MAX_SKILLS = 10;

    public const MAX_HIGHLIGHTED_SKILLS = 3;

    public const MAX_PROJECTS = 3;

    public const BIO_MAX_LENGTH = 500;

    /** Étapes du formulaire : numéro => libellé. */
    public const STEPS = [
        1 => 'Identité',
        2 => 'Métier et niveau',
        3 => 'Compétences',
        4 => 'Situation',
        5 => 'Réalisations',
        6 => 'Liens et contact',
    ];

    protected $guarded = ['id', 'user_id', 'is_featured', 'featured_at', 'completion', 'is_public'];

    protected $attributes = [
        'is_public' => false,
        'is_featured' => false,
        'completion' => 0,
    ];

    protected function casts(): array
    {
        return [
            'profession' => Profession::class,
            'level' => ExperienceLevel::class,
            'work_status' => WorkStatus::class,
            'availability' => Availability::class,
            'years_experience' => 'integer',
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
            'featured_at' => 'datetime',
            'completion' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)
            ->withPivot(['level', 'is_highlighted'])
            ->orderByPivot('is_highlighted', 'desc')
            ->orderBy('skills.name');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Profils visibles dans l'annuaire : compte validé et profil suffisamment complet. */
    public function scopeListed(Builder $query): Builder
    {
        return $query
            ->where('completion', '>=', self::minCompletion())
            ->whereHas('user', fn (Builder $q) => $q->where('status', UserStatus::Approved));
    }

    public static function minCompletion(): int
    {
        return (int) config('talent.directory_min_completion', 70);
    }

    public function isListed(): bool
    {
        return $this->completion >= self::minCompletion() && $this->user?->isApproved();
    }

    /** @return Collection<int, Skill> */
    public function highlightedSkills(): Collection
    {
        return $this->skills->filter(fn (Skill $skill) => (bool) $skill->pivot->is_highlighted)->values();
    }

    public function photoUrl(int $size = 400): ?string
    {
        return Media::url($this->photo_path, $size, $size);
    }

    /** @return array<string, string> Liens renseignés, libellé => URL. */
    public function links(): array
    {
        return array_filter([
            'LinkedIn' => $this->linkedin_url,
            'GitHub' => $this->github_url,
            'Portfolio' => $this->portfolio_url,
            'Behance' => $this->behance_url,
        ]);
    }

    /**
     * Critères de complétion, pondérés sur 100.
     *
     * @return list<array{label: string, step: int, weight: int, done: bool}>
     */
    public function completionChecklist(): array
    {
        $skills = $this->skills()->get();

        return [
            ['label' => 'Photo de profil', 'step' => 1, 'weight' => 10, 'done' => filled($this->photo_path)],
            ['label' => 'Ville', 'step' => 1, 'weight' => 5, 'done' => filled($this->city)],
            ['label' => 'Titre professionnel', 'step' => 1, 'weight' => 10, 'done' => filled($this->headline)],
            ['label' => 'Présentation', 'step' => 1, 'weight' => 10, 'done' => filled($this->bio)],
            ['label' => 'Métier principal', 'step' => 2, 'weight' => 5, 'done' => $this->profession !== null],
            ['label' => 'Niveau', 'step' => 2, 'weight' => 5, 'done' => $this->level !== null],
            ['label' => "Années d'expérience", 'step' => 2, 'weight' => 5, 'done' => $this->years_experience !== null],
            ['label' => 'Au moins 3 compétences', 'step' => 3, 'weight' => 10, 'done' => $skills->count() >= 3],
            ['label' => 'Compétences mises en avant', 'step' => 3, 'weight' => 5, 'done' => $skills->contains(fn (Skill $s) => (bool) $s->pivot->is_highlighted)],
            ['label' => 'Situation', 'step' => 4, 'weight' => 5, 'done' => $this->work_status !== null],
            ['label' => 'Disponibilité', 'step' => 4, 'weight' => 5, 'done' => $this->availability !== null],
            ['label' => 'Au moins une réalisation', 'step' => 5, 'weight' => 15, 'done' => $this->projects()->exists()],
            ['label' => 'Au moins un lien', 'step' => 6, 'weight' => 10, 'done' => $this->links() !== []],
        ];
    }

    /** Recalcule et enregistre le pourcentage de complétion. */
    public function refreshCompletion(): int
    {
        $completion = collect($this->completionChecklist())->where('done', true)->sum('weight');

        $this->forceFill(['completion' => min(100, $completion)])->saveQuietly();

        return $this->completion;
    }

    /** Mise en avant par l'admin (profils affichés en premier dans l'annuaire). */
    public function setFeatured(bool $featured): void
    {
        $this->forceFill([
            'is_featured' => $featured,
            'featured_at' => $featured ? now() : null,
        ])->save();
    }
}
