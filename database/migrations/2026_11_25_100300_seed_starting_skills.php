<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Liste de départ des catégories et compétences. Insérée par migration pour arriver
 * une seule fois en production (migrate --force au démarrage) ; l'admin la gère ensuite.
 */
return new class extends Migration
{
    /** @var array<string, list<string>> */
    private array $skills = [
        'Développement' => [
            'HTML et CSS', 'JavaScript', 'TypeScript', 'PHP', 'Laravel', 'Python', 'Django', 'Node.js',
            'React', 'Vue.js', 'Angular', 'Next.js', 'Flutter', 'React Native', 'Kotlin (Android)',
            'Swift (iOS)', 'Java', 'C# / .NET', 'Go', 'SQL', 'PostgreSQL', 'MySQL', 'MongoDB', 'API REST',
            'Git', 'Docker', 'DevOps et CI/CD', 'Cloud (AWS, GCP, Azure)', 'Tests automatisés',
            'Cybersécurité', 'WordPress',
        ],
        'Design' => [
            'UI design', 'UX design', 'Figma', 'Recherche utilisateur', 'Prototypage', 'Design system',
            'Design graphique', 'Identité visuelle', 'Adobe Photoshop', 'Adobe Illustrator', 'Motion design',
            'Canva',
        ],
        'Data et IA' => [
            'Analyse de données', 'Excel avancé', 'Power BI', 'Tableau', 'Python pour la data', 'Pandas',
            'Statistiques', 'Visualisation de données', 'Data engineering', 'Machine learning',
            'Deep learning', 'IA générative et LLM', 'Prompt engineering',
        ],
        'Gestion de projet' => [
            'Gestion de projet agile', 'Scrum', 'Kanban', 'Product management', 'Cahier des charges',
            "Gestion d'équipe", 'Relation client', 'Jira', 'Notion', 'Trello',
        ],
        'Marketing' => [
            'Marketing digital', 'SEO', 'Publicité en ligne (Meta, Google Ads)', 'Community management',
            'Copywriting', 'Création de contenu', 'Montage vidéo', 'E-mail marketing', 'Growth marketing',
            'Stratégie de marque', 'Google Analytics',
        ],
        'Outils no-code et automatisation' => [
            'Bubble', 'Webflow', 'FlutterFlow', 'Glide', 'Softr', 'Airtable', 'Zapier', 'Make', 'n8n',
            'Google Apps Script', "Automatisation avec l'IA", 'Chatbots WhatsApp',
        ],
    ];

    public function up(): void
    {
        $now = now();
        $categoryOrder = 0;

        foreach ($this->skills as $category => $names) {
            $categoryOrder += 10;

            $categoryId = DB::table('skill_categories')->insertGetId([
                'name' => $category,
                'sort_order' => $categoryOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('skills')->insert(collect($names)->values()->map(fn (string $name, int $i) => [
                'skill_category_id' => $categoryId,
                'name' => $name,
                'sort_order' => ($i + 1) * 10,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());
        }
    }

    public function down(): void
    {
        DB::table('skill_categories')->whereIn('name', array_keys($this->skills))->delete();
    }
};
