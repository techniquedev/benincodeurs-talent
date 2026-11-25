<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Réalisation d'un membre (3 maximum par profil). */
#[Fillable(['title', 'description', 'url', 'image_path', 'sort_order'])]
class Project extends Model
{
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function imageUrl(int $width = 800, int $height = 500): ?string
    {
        return Media::url($this->image_path, $width, $height);
    }
}
