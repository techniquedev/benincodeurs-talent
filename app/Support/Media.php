<?php

namespace App\Support;

use App\Filesystem\CloudinaryAdapter;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Fichiers des membres (photos, visuels de projets) sur le disque « media » :
 * Cloudinary en production, disque public en local.
 */
class Media
{
    public static function disk(): Filesystem
    {
        return Storage::disk('media');
    }

    /** Enregistre un fichier envoyé et renvoie son chemin. */
    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'media');
    }

    /**
     * URL publique d'un fichier. Sur Cloudinary, l'image est recadrée à la taille demandée
     * et servie dans le meilleur format pour le navigateur.
     */
    public static function url(?string $path, ?int $width = null, ?int $height = null): ?string
    {
        if (blank($path)) {
            return null;
        }

        $disk = Storage::disk('media');
        $adapter = $disk->getAdapter();

        if ($adapter instanceof CloudinaryAdapter && $width !== null) {
            $crop = $height !== null ? "c_fill,g_auto,w_{$width},h_{$height}" : "c_limit,w_{$width}";

            return $adapter->getUrl($path, "{$crop}/f_auto,q_auto");
        }

        return $disk->url($path);
    }

    /** Supprime un fichier sans interrompre l'action en cours si le stockage échoue. */
    public static function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        try {
            Storage::disk('media')->delete($path);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
