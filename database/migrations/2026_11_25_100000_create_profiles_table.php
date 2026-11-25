<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // 1. Identité
            $table->string('photo_path')->nullable();
            $table->string('city', 100)->nullable()->index();
            $table->string('headline', 120)->nullable();
            $table->text('bio')->nullable();

            // 2. Métier et niveau (App\Enums\Profession, App\Enums\ExperienceLevel)
            $table->string('profession', 30)->nullable()->index();
            $table->string('level', 20)->nullable()->index();
            $table->unsignedTinyInteger('years_experience')->nullable();

            // 4. Situation (App\Enums\WorkStatus, App\Enums\Availability)
            $table->string('work_status', 20)->nullable();
            $table->string('company', 150)->nullable();
            $table->boolean('company_visible')->default(true);
            $table->string('availability', 20)->nullable()->index();

            // 6. Liens et contact
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('behance_url')->nullable();
            $table->boolean('whatsapp_visible')->default(false);
            $table->boolean('email_visible')->default(false);

            // Profil public pour les entreprises : prévu pour la V2, jamais utilisé en V1.
            $table->boolean('is_public')->default(false);

            // Mise en avant par l'admin (affichés en premier dans l'annuaire).
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('featured_at')->nullable();

            // Pourcentage de complétion, recalculé à chaque enregistrement (seuil d'entrée dans l'annuaire).
            $table->unsignedTinyInteger('completion')->default(0)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
