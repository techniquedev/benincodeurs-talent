<?php

namespace App\Console\Commands;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('talent:create-admin')]
#[Description('Crée le premier administrateur à partir de ADMIN_EMAIL et ADMIN_PASSWORD, s\'il n\'existe pas encore')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = strtolower(trim((string) config('talent.admin.email')));
        $password = (string) config('talent.admin.password');

        if ($email === '' || $password === '') {
            $this->warn('ADMIN_EMAIL ou ADMIN_PASSWORD absent : aucun administrateur créé.');

            return self::SUCCESS;
        }

        if ($existing = User::where('email', $email)->first()) {
            // Le compte principal reste toujours administrateur (super-admin), même s'il a été modifié en base.
            if (! $existing->is_admin || $existing->isSuspended()) {
                $existing->forceFill(['is_admin' => true, 'status' => UserStatus::Approved, 'suspended_at' => null])->save();
            }

            $this->info("L'administrateur {$email} existe déjà : rien à faire.");

            return self::SUCCESS;
        }

        if (strlen($password) < 12) {
            $this->error('ADMIN_PASSWORD doit contenir au moins 12 caractères : administrateur non créé.');

            return self::SUCCESS; // Ne bloque pas le démarrage du site.
        }

        $admin = new User([
            'first_name' => config('talent.admin.first_name'),
            'last_name' => config('talent.admin.last_name'),
            'email' => $email,
            'password' => $password,
        ]);

        $admin->forceFill([
            'is_admin' => true,
            'status' => UserStatus::Approved,
            'approved_at' => now(),
            'email_verified_at' => now(),
        ])->save();

        $this->info("Administrateur {$email} créé.");

        return self::SUCCESS;
    }
}
