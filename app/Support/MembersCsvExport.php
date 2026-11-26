<?php

namespace App\Support;

use App\Enums\SkillLevel;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export CSV des membres (séparateur « ; » et BOM UTF-8 pour une ouverture directe dans Excel).
 */
class MembersCsvExport
{
    /** @param Builder<User> $query */
    public static function download(Builder $query): StreamedResponse
    {
        $filename = 'membres-talent-board-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::headers(), ';');

            $query->clone()
                ->with(['profile.skills', 'profile.projects'])
                ->reorder()->orderBy('users.id')
                ->chunk(200, function ($users) use ($out) {
                    foreach ($users as $user) {
                        fputcsv($out, self::row($user), ';');
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return list<string> */
    private static function headers(): array
    {
        return [
            'ID', 'Prénom', 'Nom', 'E-mail', 'WhatsApp', 'Statut', 'E-mail vérifié', 'Inscrit le', 'Validé le',
            'Ville', 'Titre', 'Métier', 'Niveau', "Années d'expérience", 'Situation', 'Entreprise', 'Disponibilité',
            'Compétences', 'Compétences mises en avant', 'Réalisations', 'LinkedIn', 'GitHub', 'Portfolio', 'Behance',
            'Complétion (%)', "Dans l'annuaire", 'Mis en avant',
        ];
    }

    /** @return list<string|int|null> */
    private static function row(User $user): array
    {
        /** @var Profile|null $profile */
        $profile = $user->profile;
        $skills = $profile?->skills ?? collect();
        $yesNo = fn (?bool $value) => $value ? 'Oui' : 'Non';

        return [
            $user->id,
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->whatsapp,
            $user->status->label(),
            $yesNo($user->email_verified_at !== null),
            $user->created_at?->format('d/m/Y H:i'),
            $user->approved_at?->format('d/m/Y H:i'),
            $profile?->city,
            $profile?->headline,
            $profile?->profession?->label(),
            $profile?->level?->label(),
            $profile?->years_experience,
            $profile?->work_status?->label(),
            $profile?->company,
            $profile?->availability?->label(),
            $skills->map(fn ($skill) => $skill->name.' ('.(SkillLevel::tryFrom($skill->pivot->level)?->label() ?? '?').')')->join(', '),
            $skills->filter(fn ($skill) => $skill->pivot->is_highlighted)->pluck('name')->join(', '),
            $profile?->projects->count() ?? 0,
            $profile?->linkedin_url,
            $profile?->github_url,
            $profile?->portfolio_url,
            $profile?->behance_url,
            $profile?->completion ?? 0,
            $yesNo($profile?->isListed()),
            $yesNo($profile?->is_featured),
        ];
    }
}
