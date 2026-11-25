# Talent Board · Étape 1 : socle, comptes, déploiement Render

## Ce qui est en place

- **Laravel 13.44**, **Filament 5.12** (admin `/admin`), **Livewire 4**, **Tailwind 4**. Français, fuseau `Africa/Porto-Novo`, police Inter, charte Bénin Codeurs (violet `#280050` / `#1a0035`, vert-jaune `#D4F01F`, coins arrondis), mobile d'abord.
- Code compatible **SQLite** (local) et **PostgreSQL** (Render) : migrations standard, aucune requête propre à une base.
- **Comptes** :
  - inscription (prénom, nom, e-mail, WhatsApp avec `+229` par défaut, validé par `propaganistas/laravel-phone` et enregistré au format E.164, unique) ;
  - vérification de l'e-mail, mot de passe oublié / réinitialisation, limitation des tentatives de connexion ;
  - statuts `pending` → `approved` → `suspended` (`App\Enums\UserStatus`). Un compte suspendu est déconnecté immédiatement ;
  - page « Ton compte est en cours de validation par l'équipe » (`/compte/en-attente`), et l'annuaire `/annuaire` réservé aux comptes validés (page d'attente pour l'instant) ;
  - e-mail « Ton compte Talent Board est validé » envoyé par `User::approve()` ;
  - rôle admin séparé (`is_admin`). Seuls les admins entrent dans `/admin`. Le premier admin est créé au démarrage par `php artisan talent:create-admin` à partir de `ADMIN_EMAIL` / `ADMIN_PASSWORD` (12 caractères minimum), s'il n'existe pas déjà ;
  - colonnes prévues pour la vérification VIP automatique (V2) : `vip_verification_method`, `vip_verified_at`.
- **Admin minimal** : page Membres (liste, recherche, filtres statut / e-mail vérifié / admin, fiche modifiable, actions **Valider** et **Suspendre**, badge du nombre de comptes en attente). Le reste de l'admin (compétences, tableau de bord, export CSV) viendra plus tard.
- **Fichiers** : disque `media`, Cloudinary si `CLOUDINARY_URL` est défini, sinon repli sur le disque `public`. Le paquet `cloudinary-labs/cloudinary-laravel` n'accepte pas encore Laravel 13 : l'adaptateur est écrit dans le projet (`app/Filesystem/CloudinaryAdapter.php`, SDK officiel `cloudinary/cloudinary_php`, images uniquement). Les envois Filament utilisent ce disque par défaut.
- **E-mails** : `MAIL_MAILER` = `log` (local), `smtp`, `brevo` (API HTTP, `BREVO_API_KEY`) ou `resend` (API HTTP, `RESEND_API_KEY`). Les e-mails sont envoyés tout de suite, sans file d'attente.
- **Render** : `Dockerfile` (PHP 8.4 FPM + Nginx + Supervisor, assets compilés au build), `docker/start.sh` (`optimize`, `migrate --force` avec plusieurs essais, création de l'admin), `render.yaml` (service web Docker + base PostgreSQL), `.env.example` sans aucun secret.

Vérifié en local : toutes les pages répondent, les pages protégées redirigent vers la connexion, une inscription crée un compte « en attente » et envoie l'e-mail de vérification, un numéro invalide est refusé avec le message en français, la validation envoie l'e-mail de compte validé, l'admin accède au panneau, et l'URL Cloudinary est bien construite. **Non vérifiés** (impossible sans les vrais comptes) : la construction de l'image Docker, un envoi réel vers Cloudinary, l'envoi réel par Brevo ou Resend.

## Lancer en local

Prérequis : PHP 8.4 (avec `pdo_sqlite`, `intl`, `zip`), Composer, Node 24.
Sur cette machine, Composer est dans `C:\Users\YALINMON JUSTIN\bin\composer.phar` (alias `composer` dans Git Bash).

```bash
cd C:\develop\Talent
composer install
npm install
cp .env.example .env              # puis renseigner ADMIN_EMAIL et ADMIN_PASSWORD
php artisan key:generate
touch database/database.sqlite     # PowerShell : New-Item database\database.sqlite
php artisan migrate
php artisan talent:create-admin
php artisan storage:link
npm run build                      # ou « npm run dev » pendant le développement
php artisan serve
```

- Site : http://localhost:8000. Admin : http://localhost:8000/admin.
- Avec `MAIL_MAILER=log`, les e-mails (vérification, mot de passe oublié, validation) sont écrits dans `storage/logs/laravel.log` : copier le lien depuis ce fichier.
- Valider un compte : `/admin` → Membres → **Valider**.

## Variables à saisir dans Render

Déjà remplies par `render.yaml` : `APP_NAME`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_LOCALE`, `APP_TIMEZONE`, `LOG_CHANNEL`, `LOG_LEVEL`, `DB_CONNECTION=pgsql`, `DB_URL` (liée à la base), `SESSION_DRIVER`, `SESSION_SECURE_COOKIE`, `CACHE_STORE`, `QUEUE_CONNECTION=sync`, `CLOUDINARY_FOLDER`, `MAIL_FROM_NAME`.

À saisir :

| Variable | Valeur |
|---|---|
| `APP_KEY` | sortie de `php artisan key:generate --show` (en local), commence par `base64:` |
| `APP_URL` | URL du service, ex. `https://talent-board.onrender.com` (si vide, l'URL fournie par Render est utilisée) |
| `CLOUDINARY_URL` | Cloudinary → Dashboard → « API environment variable » : `cloudinary://<api_key>:<api_secret>@<cloud_name>` |
| `MAIL_MAILER` | `brevo` ou `resend` (recommandé sur Render), ou `smtp` |
| `BREVO_API_KEY` | si `brevo` : Brevo → SMTP & API → Clés API |
| `RESEND_API_KEY` | si `resend` : Resend → API Keys |
| `MAIL_FROM_ADDRESS` | adresse d'expéditeur sur un domaine validé chez Brevo ou Resend |
| `ADMIN_EMAIL` | e-mail du premier administrateur |
| `ADMIN_PASSWORD` | mot de passe du premier admin, 12 caractères minimum |

Pour du SMTP (si l'offre Render le permet) : `MAIL_MAILER=smtp` + `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME` (à ajouter à la main dans Environment). Les variables `sync: false` laissées vides peuvent être remplies plus tard dans **Environment**, puis **Manual Deploy**.

## Connecter GitHub et Render

1. **Créer le dépôt GitHub** : github.com → **New repository** → nom `talent` (privé), **sans** README, .gitignore ni licence.
2. **Pousser le code** (depuis `C:\develop\Talent`) :
   ```bash
   git remote add origin https://github.com/<compte>/talent.git
   git push -u origin main
   ```
3. **Render** : dashboard.render.com → **New +** → **Blueprint**.
4. **Connect GitHub** (la première fois) : autoriser l'application Render, puis donner accès au dépôt `talent` (« Only select repositories »).
5. Choisir le dépôt `talent`, branche `main`. Render lit `render.yaml` et affiche la base `talent-db` et le service `talent-board`.
6. Donner un nom au Blueprint, saisir les variables du tableau ci-dessus, puis **Apply**.
7. Render crée la base, construit l'image Docker (quelques minutes) et démarre le service. Dans **Logs**, on doit voir les migrations puis « Administrateur … créé ».
8. Ouvrir l'URL `https://talent-board.onrender.com` (ou celle affichée), puis `/admin` pour se connecter avec `ADMIN_EMAIL` / `ADMIN_PASSWORD`.
9. Ensuite, chaque `git push` sur `main` redéploie automatiquement (`autoDeploy: true`).

Points d'attention :
- **Base gratuite Render : elle expire au bout de 30 jours.** Pour la production, passer `talent-db` en offre payante (dans Render ou en changeant `plan` dans `render.yaml`).
- Le service web gratuit se met en veille après 15 min sans visite (premier chargement lent). L'offre Starter évite cela.
- Si `CLOUDINARY_URL` est vide en production, les fichiers iront sur le disque du conteneur et **seront perdus à chaque déploiement**.
- Région choisie : Frankfurt (la plus proche du Bénin). On peut la modifier dans `render.yaml` **avant** la première création.

## Fichiers principaux

- `app/Models/User.php`, `app/Enums/UserStatus.php`, `database/migrations/0001_01_01_000000_create_users_table.php`
- `app/Livewire/Auth/*` + `resources/views/livewire/auth/*` (inscription, connexion, mot de passe)
- `app/Http/Controllers/AccountController.php`, `routes/web.php`, `app/Http/Middleware/*`
- `app/Notifications/*` (e-mails en français), `lang/fr/*`, `lang/fr.json`
- `app/Console/Commands/CreateAdmin.php`, `config/talent.php`
- `app/Filament/Resources/Users/*`, `app/Providers/Filament/AdminPanelProvider.php`
- `app/Filesystem/CloudinaryAdapter.php`, `app/Providers/AppServiceProvider.php`, `config/filesystems.php`
- `Dockerfile`, `docker/*`, `render.yaml`, `.env.example`
