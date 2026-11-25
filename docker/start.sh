#!/bin/sh
# Démarrage du conteneur sur Render : configuration, cache, migrations, premier admin, puis Nginx + PHP-FPM.
set -e
cd /var/www/html

export PORT="${PORT:-10000}"

# Render fournit l'URL publique du service : on l'utilise si APP_URL n'est pas saisi.
if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

if [ -z "$APP_KEY" ]; then
    echo "ERREUR : APP_KEY est vide. Génère-la avec « php artisan key:generate --show » et saisis-la dans Render."
    exit 1
fi

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

# Repli local du disque « media » (sans Cloudinary) : lien public/storage.
php artisan storage:link --force >/dev/null 2>&1 || true

# Mise en cache de la configuration, des routes, des vues et des événements.
php artisan optimize

# Migrations : quelques essais, le temps que la base PostgreSQL soit joignable.
attempt=1
until php artisan migrate --force; do
    if [ "$attempt" -ge 10 ]; then
        echo "ERREUR : migrations impossibles après $attempt essais."
        exit 1
    fi
    echo "Base de données indisponible, nouvel essai dans 3 s ($attempt/10)…"
    attempt=$((attempt + 1))
    sleep 3
done

# Premier administrateur (ADMIN_EMAIL / ADMIN_PASSWORD), seulement s'il n'existe pas.
php artisan talent:create-admin

chown -R www-data:www-data storage bootstrap/cache

exec /usr/bin/supervisord -c /etc/supervisord.conf
