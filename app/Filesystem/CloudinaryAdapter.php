<?php

namespace App\Filesystem;

use Cloudinary\Api\Exception\NotFound;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Http;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\Visibility;
use Throwable;

/**
 * Disque Flysystem minimal pour Cloudinary (images uniquement : photos, visuels de projets).
 *
 * Le chemin stocké en base garde son extension (ex. « photos/abc.jpg ») ; l'identifiant
 * Cloudinary est ce chemin sans extension, préfixé par le dossier configuré
 * (ex. « talent/photos/abc »). L'URL publique est l'URL de livraison Cloudinary.
 *
 * Écrit à la main car cloudinary-labs/cloudinary-laravel ne prend pas encore en charge Laravel 13.
 */
class CloudinaryAdapter implements FilesystemAdapter
{
    public function __construct(
        private readonly Cloudinary $cloudinary,
        private readonly string $folder = '',
    ) {}

    public static function fromUrl(string $cloudinaryUrl, string $folder = ''): self
    {
        return new self(new Cloudinary($cloudinaryUrl), trim($folder, '/'));
    }

    /** Identifiant Cloudinary (sans extension) d'un chemin Laravel. */
    public function publicId(string $path): string
    {
        $path = ltrim($path, '/');
        $info = pathinfo($path);
        $dir = ($info['dirname'] ?? '.') === '.' ? '' : $info['dirname'].'/';
        $id = $dir.$info['filename'];

        return $this->folder !== '' ? $this->folder.'/'.$id : $id;
    }

    /**
     * Utilisé par Storage::disk('cloudinary')->url($path). La transformation facultative
     * est au format URL Cloudinary (ex. « c_fill,w_400,h_400/f_auto,q_auto »).
     */
    public function getUrl(string $path, ?string $transformation = null): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $asset = $this->cloudinary->image($this->publicId($path));

        if ($transformation !== null && $transformation !== '') {
            $asset->addTransformation($transformation);
        }

        if ($extension !== '') {
            $asset->extension($extension);
        }

        return (string) $asset->toUrl();
    }

    public function fileExists(string $path): bool
    {
        try {
            return Http::timeout(10)->head($this->getUrl($path))->successful();
        } catch (Throwable $e) {
            throw UnableToCheckExistence::forLocation($path, $e);
        }
    }

    public function directoryExists(string $path): bool
    {
        return true; // Cloudinary n'a pas de vrais dossiers.
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'cld');

        try {
            file_put_contents($tmp, $contents);
            $this->upload($path, $tmp);
        } finally {
            @unlink($tmp);
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'cld');

        try {
            $out = fopen($tmp, 'wb');
            stream_copy_to_stream($contents, $out);
            fclose($out);
            $this->upload($path, $tmp);
        } finally {
            @unlink($tmp);
        }
    }

    private function upload(string $path, string $localFile): void
    {
        try {
            $this->cloudinary->uploadApi()->upload($localFile, [
                'public_id' => $this->publicId($path),
                'resource_type' => 'image',
                'overwrite' => true,
                'invalidate' => true,
                // Les photos de téléphone sont souvent énormes : on garde 1600 px maximum.
                'transformation' => [['width' => 1600, 'height' => 1600, 'crop' => 'limit']],
            ]);
        } catch (Throwable $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function read(string $path): string
    {
        $response = Http::timeout(30)->get($this->getUrl($path));

        if (! $response->successful()) {
            throw UnableToReadFile::fromLocation($path, 'HTTP '.$response->status());
        }

        return $response->body();
    }

    public function readStream(string $path)
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $this->read($path));
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        try {
            $this->cloudinary->uploadApi()->destroy($this->publicId($path), [
                'resource_type' => 'image',
                'invalidate' => true,
            ]);
        } catch (Throwable $e) {
            throw UnableToDeleteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function deleteDirectory(string $path): void
    {
        $prefix = $this->publicId(rtrim($path, '/').'/x');
        $prefix = substr($prefix, 0, -1);

        try {
            $this->cloudinary->adminApi()->deleteAssetsByPrefix($prefix, ['resource_type' => 'image']);
        } catch (Throwable $e) {
            throw UnableToDeleteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Rien à faire : les dossiers sont créés à l'envoi.
    }

    public function setVisibility(string $path, string $visibility): void
    {
        if ($visibility !== Visibility::PUBLIC) {
            throw UnableToSetVisibility::atLocation($path, 'Les fichiers Cloudinary de ce disque sont toujours publics.');
        }
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, Visibility::PUBLIC);
    }

    public function mimeType(string $path): FileAttributes
    {
        return new FileAttributes($path, null, null, null, $this->head($path, 'Content-Type', 'mimeType'));
    }

    public function lastModified(string $path): FileAttributes
    {
        $value = $this->head($path, 'Last-Modified', 'lastModified');

        return new FileAttributes($path, null, null, strtotime($value) ?: null);
    }

    public function fileSize(string $path): FileAttributes
    {
        return new FileAttributes($path, (int) $this->head($path, 'Content-Length', 'fileSize'));
    }

    private function head(string $path, string $header, string $type): string
    {
        try {
            $response = Http::timeout(10)->head($this->getUrl($path));
        } catch (Throwable $e) {
            throw UnableToRetrieveMetadata::create($path, $type, $e->getMessage(), $e);
        }

        if (! $response->successful() || $response->header($header) === '') {
            throw UnableToRetrieveMetadata::create($path, $type, 'HTTP '.$response->status());
        }

        return $response->header($header);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $prefix = trim($this->folder.'/'.trim($path, '/'), '/');
        $cursor = null;

        do {
            try {
                $result = $this->cloudinary->adminApi()->assets(array_filter([
                    'type' => 'upload',
                    'resource_type' => 'image',
                    'prefix' => $prefix === '' ? null : $prefix.'/',
                    'max_results' => 500,
                    'next_cursor' => $cursor,
                ]));
            } catch (NotFound) {
                return;
            }

            foreach ($result['resources'] ?? [] as $resource) {
                $relative = ltrim(substr($resource['public_id'], strlen($this->folder)), '/');

                if (! $deep && str_contains(ltrim(substr($relative, strlen(trim($path, '/'))), '/'), '/')) {
                    continue;
                }

                yield new FileAttributes(
                    $relative.'.'.$resource['format'],
                    $resource['bytes'] ?? null,
                    Visibility::PUBLIC,
                    isset($resource['created_at']) ? strtotime($resource['created_at']) : null,
                );
            }

            $cursor = $result['next_cursor'] ?? null;
        } while ($cursor);
    }

    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $this->cloudinary->uploadApi()->rename($this->publicId($source), $this->publicId($destination), [
                'resource_type' => 'image',
                'overwrite' => true,
            ]);
        } catch (Throwable $e) {
            throw UnableToMoveFile::fromLocationTo($source, $destination, $e);
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $this->cloudinary->uploadApi()->upload($this->getUrl($source), [
                'public_id' => $this->publicId($destination),
                'resource_type' => 'image',
                'overwrite' => true,
            ]);
        } catch (Throwable $e) {
            throw UnableToCopyFile::fromLocationTo($source, $destination, $e);
        }
    }
}
