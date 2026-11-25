# Bénin Codeurs Talent Board : brief V1

## Objectif
Un annuaire des membres de la communauté VIP Bénin Codeurs. Chaque membre crée un compte, remplit son profil (métier, niveau, compétences, situation, réalisations) et apparaît dans l'annuaire après validation par l'équipe.

## Décisions
- Projet séparé : `C:\develop\talent`, avec son propre dépôt GitHub.
- Laravel (dernière version stable), Filament (admin), Livewire, Tailwind.
- Hébergement : **Render** avec un **Dockerfile** (PHP 8.4 + Nginx ou équivalent prêt pour la production). Base **PostgreSQL** Render. Les migrations sont lancées au démarrage du conteneur.
- Fichiers (photos, images de projets) : **Cloudinary**, jamais le disque local, car le disque de Render est effacé à chaque déploiement.
- Langue : français. Fuseau : Africa/Porto-Novo. Design : charte Bénin Codeurs (violet foncé, vert-jaune #D4F01F, coins arrondis), responsive, mobile d'abord.
- Annuaire visible **uniquement par les membres connectés et validés**. Prévoir le champ « profil public » sans l'activer (V2).
- Vérification VIP : **validation manuelle par l'admin** (V1). Prévoir un emplacement pour une vérification automatique plus tard.
- Aucun secret dans le code ni dans Git : tout passe par les variables d'environnement.

## Comptes
- Inscription : prénom, nom, e-mail, numéro WhatsApp (format international, +229 par défaut, validé avec propaganistas/laravel-phone), mot de passe.
- Vérification de l'e-mail, mot de passe oublié.
- Statuts : en attente → validé → suspendu. Un compte en attente peut remplir son profil, mais ne voit pas l'annuaire (message : « Ton compte est en cours de validation par l'équipe »).
- E-mail envoyé au membre quand son compte est validé.

## Profil (formulaire en étapes, avec sauvegarde à chaque étape)
1. **Identité** : photo, ville, titre professionnel, présentation (500 caractères max).
2. **Métier et niveau** : métier principal (Développeur, Designer, Data ou IA, Chef de projet ou product, Marketing digital ou contenu, Autre), niveau (Explorateur, Praticien, Confirmé, Senior, avec une ligne d'explication pour chacun), années d'expérience.
3. **Compétences** : choisies dans une liste gérée par l'admin, regroupées par catégorie, avec un niveau chacune (débutant, intermédiaire, avancé, expert). 10 maximum, dont 3 mises en avant.
4. **Situation** : salarié, freelance, entrepreneur, étudiant, en recherche. Entreprise actuelle (facultative) avec un choix « visible / masquée ». Disponibilité : ouvert aux missions, ouvert à un emploi, pas disponible.
5. **Réalisations** : 3 projets maximum (titre, description, lien, image).
6. **Liens et contact** : LinkedIn, GitHub, portfolio, Behance. WhatsApp et e-mail, chacun visible ou masqué pour les autres membres.

Une jauge de complétion est affichée. Le profil entre dans l'annuaire seulement si le compte est validé et le profil complet à 70 % ou plus.

## Annuaire
- Cartes : photo, nom, titre, niveau, 3 compétences mises en avant, badge de disponibilité, ville.
- Recherche texte et filtres : métier, compétence, niveau, ville, disponibilité. Pagination.
- Page de profil complète, en respectant les choix de visibilité (entreprise, WhatsApp, e-mail).
- Profils mis en avant par l'admin affichés en premier.

## Admin (Filament, /admin)
- Membres : liste, filtres, fiche, actions Valider / Suspendre / Mettre en avant.
- Compétences et catégories : gestion complète. Pré-remplir une liste de départ (développement, design, data et IA, gestion de projet, marketing, outils no-code et automatisation).
- Tableau de bord : membres en attente, validés, par métier, par niveau, par disponibilité.
- Export CSV des membres.

## Hors V1 (à prévoir plus tard)
Profil public pour les entreprises, vérification VIP automatique avec le site principal, offres de missions, recommandations entre membres, import du résultat du bilan.
