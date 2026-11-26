<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/** Métier principal d'un membre. */
enum Profession: string
{
    use HasOptions;

    case Developer = 'developer';
    case Designer = 'designer';
    case DataAi = 'data_ai';
    case ProjectProduct = 'project_product';
    case Marketing = 'marketing';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Developer => 'Développeur',
            self::Designer => 'Designer',
            self::DataAi => 'Data ou IA',
            self::ProjectProduct => 'Chef de projet ou product',
            self::Marketing => 'Marketing digital ou contenu',
            self::Other => 'Autre',
        };
    }

    /**
     * Compétences suggérées en premier à l'étape « Compétences » (noms de la liste de départ).
     * Les noms absents de la base (renommés ou supprimés par l'admin) sont ignorés.
     *
     * @return list<string>
     */
    public function suggestedSkillNames(): array
    {
        return match ($this) {
            self::Developer => ['JavaScript', 'PHP', 'Laravel', 'React', 'Node.js', 'Python', 'SQL', 'Git'],
            self::Designer => ['Figma', 'UI design', 'UX design', 'Prototypage', 'Design system', 'Design graphique', 'Identité visuelle', 'Adobe Illustrator'],
            self::DataAi => ['Analyse de données', 'Python pour la data', 'SQL', 'Power BI', 'Machine learning', 'IA générative et LLM', 'Visualisation de données', 'Excel avancé'],
            self::ProjectProduct => ['Gestion de projet agile', 'Scrum', 'Product management', 'Cahier des charges', "Gestion d'équipe", 'Relation client', 'Jira', 'Notion'],
            self::Marketing => ['Marketing digital', 'Community management', 'SEO', 'Copywriting', 'Création de contenu', 'Publicité en ligne (Meta, Google Ads)', 'Google Analytics', 'Canva'],
            self::Other => ["Automatisation avec l'IA", 'Excel avancé', 'Canva', 'Notion', 'Gestion de projet agile', 'Création de contenu', 'Relation client', 'Zapier'],
        };
    }
}
