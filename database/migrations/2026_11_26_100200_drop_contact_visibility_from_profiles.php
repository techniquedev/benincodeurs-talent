<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Les profils ne sont vus que par l'équipe Bénin Codeurs : plus de réglage de visibilité des contacts. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['company_visible', 'whatsapp_visible', 'email_visible']);
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('company_visible')->default(true);
            $table->boolean('whatsapp_visible')->default(false);
            $table->boolean('email_visible')->default(false);
        });
    }
};
