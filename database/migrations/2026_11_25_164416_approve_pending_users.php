<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La validation manuelle disparaît : l'adhésion VIP est vérifiée par l'API à l'inscription.
 * Les comptes encore « en attente » passent à « validé » (sans e-mail de notification).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_at' => DB::raw('COALESCE(approved_at, CURRENT_TIMESTAMP)'),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Irréversible : on ne sait plus quels comptes étaient en attente.
    }
};
