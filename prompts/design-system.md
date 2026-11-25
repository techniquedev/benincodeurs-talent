# Charte graphique : Talent Board Bénin Codeurs

Objectif : un site moins blanc, plus marqué, qui alterne fonds sombres violets et fonds clairs lavande.

## Couleurs (aucune autre)

| Jeton Tailwind | Valeur    | Usage |
|----------------|-----------|-------|
| `night`        | `#1A0035` | Violet nuit : fonds sombres, barre de navigation, footer, écrans de connexion |
| `brand`        | `#280050` | Violet profond : sections sombres, en-têtes de page de l'espace membre |
| `violet`       | `#6B21A8` | Violet action : boutons et liens sur fond clair |
| `lime`         | `#D4F01F` | Vert-jaune, accent : boutons sur fond sombre, badges, progression |
| `paper`        | `#F6F5FA` | Gris lavande : fonds clairs |
| `white`        | `#FFFFFF` | Cartes |
| `line`         | `#E7E5EE` | Bordures |
| `ink`          | `#1A1523` | Texte principal |
| `muted`        | `#6B6578` | Texte secondaire |

Les variantes avec opacité de ces couleurs (`white/70`, `violet/10`…) sont autorisées.
Seule exception : le rouge des messages d'erreur de formulaire (`red-700`), pour que l'erreur reste reconnaissable.

### Règles du vert-jaune
- Jamais en aplat (pas de grand bloc ni de forme décorative pleine).
- Jamais de texte vert-jaune sur fond clair. Sur fond sombre, autorisé pour un mot mis en valeur ou un survol.
- Sur fond clair, réservé à la progression, au badge « disponible » (ouvert aux missions) et aux statuts positifs (compte validé, profil visible).

### Contraste (WCAG AA)
- Blanc sur `night`, `brand` ou `violet` : > 8:1.
- `ink` sur blanc ou `paper` : > 15:1. `muted` sur blanc : 5,6:1 ; sur `paper` : 5,1:1.
- `night` sur `lime` : 14:1.
- Texte secondaire sur fond sombre : `white/70` minimum (jamais en dessous pour du texte courant).

## Boutons

| Fond   | Principal | Secondaire |
|--------|-----------|------------|
| Sombre | `.btn-lime` : fond vert-jaune, texte `night` | `.btn-ghost-light` : contour blanc, texte blanc |
| Clair  | `.btn-primary` : fond `violet`, texte blanc (survol `brand`) | `.btn-secondary` : fond blanc, contour gris (`muted/40`), texte `ink` |

Coins `rounded-lg`, police Inter, graisse 600. Sur mobile, les boutons principaux prennent toute la largeur.

## Par type de page

### Accueil (alternance sombre / clair)
1. **Hero** sombre : dégradé `night → brand`, lueur violette discrète, texte blanc, photo à droite (dessous sur mobile). Pas de bande de chiffres.
2. **Comment ça marche** clair : fond `paper`, cartes blanches.
3. **Pensé pour la communauté** sombre : fond `brand`. Sur ordinateur, partie collante visible dès l'entrée dans la section, 1er point actif d'emblée, environ un écran de défilement par point, pas de zone vide. Sur mobile, cartes empilées.
4. **Appel final** : carte violette avec photo assombrie, sur fond `paper`.
5. **Footer** `night`.

### Barre de navigation (pages publiques)
- Transparente au-dessus du hero sombre (logo et texte clairs), pleine `night` dès qu'on défile.
- Jamais semi-transparente au-dessus du texte. Aucune bande blanche au-dessus du hero.
- Sans JavaScript, ou sur les pages sans hero : pleine `night`.

### Connexion, inscription, mot de passe, confirmation d'e-mail
Fond `night` plein écran avec légère lueur violette, logo version claire au-dessus, carte blanche centrée.

### Espace membre, profil, annuaire
- Barre de navigation `night`, puis en-tête de page `brand` (titre blanc, texte `white/70`) : composant `<x-page-header>`.
- Contenu sur `paper`, cartes blanches à bordure fine `line`.

### Pages légales
En-tête `brand`, texte dans une carte blanche sur `paper`.

### Pages d'erreur
Barre `night` avec logo clair, contenu sur `paper`, bouton principal `violet`.

### Admin Filament
Primaire `#6B21A8`, neutres gris, police Inter, logo Bénin Codeurs. Fond `paper`, bordures `line`.

## Responsive
Mobile d'abord. Zones tactiles de 40 px minimum, menus en panneau plein écran sur mobile, grilles qui passent à une colonne, textes de titres réduits sous 640 px.
