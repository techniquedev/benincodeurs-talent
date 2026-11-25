<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'cloudinary' => [
            'driver' => 'cloudinary', // App\Filesystem\CloudinaryAdapter, enregistré dans AppServiceProvider
            'url' => env('CLOUDINARY_URL'), // cloudinary://<api_key>:<api_secret>@<cloud_name>
            'folder' => env('CLOUDINARY_FOLDER', 'talent'),
            'visibility' => 'public',
            'throw' => true,
            'report' => true,
        ],

        /*
        | Disque des fichiers des membres (photos, visuels de projets) : toujours l'utiliser.
        | Cloudinary si CLOUDINARY_URL est défini, sinon repli sur le disque public (local uniquement :
        | le disque de Render est effacé à chaque déploiement).
        */
        'media' => env('CLOUDINARY_URL') ? [
            'driver' => 'cloudinary',
            'url' => env('CLOUDINARY_URL'),
            'folder' => env('CLOUDINARY_FOLDER', 'talent'),
            'visibility' => 'public',
            'throw' => true,
            'report' => true,
        ] : [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
