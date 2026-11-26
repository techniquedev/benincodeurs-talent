<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /** Fiche d'un membre dans l'annuaire, réservée à l'équipe (permission « directory.view », voir routes). */
    public function show(Request $request, Profile $profile): View
    {
        return $this->render($profile, $profile->user_id === $request->user()->id);
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
