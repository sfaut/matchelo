# Matchelo

Gestion de clubs d'échecs scolaires : présences, tournois, résultats et classement ELO.
Laravel 13, Livewire 4, Tailwind CSS 4, Pest.

## Installation

```shell
composer setup
```

Le script installe les dépendances, crée `.env`, génère la clef d'application, lance les
migrations, crée le lien de stockage public (voir ci-dessous) et compile les assets.

Développement : `composer run dev`. Tests : `composer test` (Pint, PHPStan, Pest, tests JavaScript).

## Lien de stockage public (`storage:link`)

### Pourquoi

Les fichiers envoyés par les utilisateurs, comme les logos de club, sont enregistrés sur le
disque `public` de Laravel, c'est-à-dire dans `storage/app/public` (logos :
`storage/app/public/clubs/logos/`). Ce dossier se trouve hors de `public/`, la racine servie par
le serveur web : sans intervention, ces fichiers ne sont pas accessibles par URL.

`php artisan storage:link` crée le lien symbolique `public/storage` → `storage/app/public`.
L'URL `/storage/clubs/logos/12-ab3f9k2x.webp` sert alors le fichier correspondant ; c'est
l'URL que produit `Storage::disk('public')->url($path)` (méthode `Club::logoUrl()`).

### Quand le créer

Le lien n'est pas versionné (`/public/storage` figure dans `.gitignore`) : il se crée sur
chaque machine et dans chaque environnement.

- **Installation locale** : `composer setup` l'exécute.
- **Déploiement** : ajouter `php artisan storage:link` aux commandes de déploiement de chaque
  environnement (préproduction, production), après l'installation des dépendances.
- **Commande sans risque** : si le lien existe déjà, elle l'indique et se termine sans erreur ;
  `--force` recrée un lien existant.

Les fichiers de `storage/app/public` ne sont pas versionnés non plus : en production, ce dossier
doit être persistant entre deux déploiements (volume ou disque partagé), sinon les logos sont
perdus. Pour un stockage objet (S3…), configurer le disque `public` en conséquence ; le lien
devient alors inutile.

### Windows

Créer un lien symbolique sous Windows demande le mode développeur ou une console
administrateur. Si la commande échoue, l'activer puis relancer `php artisan storage:link`.

### Tests

Les tests n'utilisent pas le lien : ils remplacent le disque par un disque factice
(`Storage::fake('public')`).
