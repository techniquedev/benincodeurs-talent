<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /** Fiche d'un membre dans l'annuaire (réservée aux membres validés). */
    public function show(Request $request, Profile $profile): View
    {
        $viewer = $request->user();
        $isOwner = $profile->user_id === $viewer->id;

        // Un profil non visible dans l'annuaire n'existe pas pour les autres membres.
        abort_unless($profile->isListed() || $isOwner || $viewer->is_admin, 404);

        return $this->render($profile, $isOwner);
    }

    /** Aperçu de son propre profil, possible même avant la validation du compte. */
    public function preview(Request $request): View
    {
        return $this->render($request->user()->ensureProfile(), true);
    }

    private function render(Profile $profile, bool $isOwner): View
    {
        $profile->load(['user', 'skills.category', 'projects']);

        return view('profile.show', [
            'profile' => $profile,
            'user' => $profile->user,
            'isOwner' => $isOwner,
        ]);
    }
}
