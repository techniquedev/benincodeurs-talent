<?php

namespace App\Providers;

use App\Filesystem\CloudinaryAdapter;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use League\Flysystem\Filesystem;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        // <x-layouts::app> et <x-layouts::guest> pour les pages Blade classiques.
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');

        // Derrière le proxy HTTPS de Render : toutes les URL générées en https.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Le super-admin (ADMIN_EMAIL) a tous les droits de l'admin, quels que soient ses rôles.
        Gate::before(fn (User $user) => $user->isSuperAdmin() ? true : null);

        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        // Disque Cloudinary (voir config/filesystems.php, disques « cloudinary » et « media »).
        Storage::extend('cloudinary', function ($app, array $config) {
            $adapter = CloudinaryAdapter::fromUrl((string) $config['url'], (string) ($config['folder'] ?? ''));

            return new FilesystemAdapter(new Filesystem($adapter, $config), $adapter, $config);
        });

        // Les envois de fichiers Filament vont sur le disque « media » (Cloudinary ou repli public).
        FileUpload::configureUsing(fn (FileUpload $upload) => $upload->disk('media')->visibility('public'));

        // Transport e-mail par l'API HTTP de Brevo (MAIL_MAILER=brevo, BREVO_API_KEY).
        Mail::extend('brevo', fn () => (new BrevoTransportFactory)->create(
            new Dsn('brevo+api', 'default', (string) config('services.brevo.key'))
        ));
    }
}
