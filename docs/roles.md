# Rôles utilisateur

Ce document décrit les rôles métier de Matchelo et les droits qui en découlent. Le code reste la
référence pour les valeurs (codes, identifiants) : ce fichier y renvoie sans les recopier.

## Principe

Matchelo distingue deux niveaux :

- **l'administrateur**, global à l'application, signalé par l'indicateur `users.is_administrator` ;
- **les rôles de club** (président, animateur, joueur), portés par la table pivot `club_user`
  (`club_user.role_id` → `roles.id`).

La clef primaire `(club_id, user_id)` de `club_user` limite chaque utilisateur à **un seul rôle
par club** ; il peut tenir des rôles différents dans des clubs différents (animateur ici, joueur
là).

**Seuls les utilisateurs connectés agissent dans l'application**, ce qui suppose un compte avec
e-mail. Un joueur sans compte (`users.email` à `NULL`) figure dans son club et au classement,
mais n'interagit pas : l'animateur agit pour lui.

Références dans le code :

- table `roles` et indicateur `is_administrator` :
  `database/migrations/0001_01_01_000000_create_users_table.php` ;
- valeurs des rôles : `database/seeders/RoleSeeder.php` ;
- constantes et recherche par code : `App\Models\User\Role` (`Role::PRESIDENT`, `Role::ANIMATOR`,
  `Role::PLAYER`, `Role::MANAGERS`, `Role::idFor()`, `Role::idsFor()`) ;
- rôles d'un utilisateur : `User::managedClubs()`, `User::isManager()`, `User::presides()` ;
- droits sur les clubs : `App\Policies\ClubPolicy`.

## Administrateur

Administre l'application. Il dispose de tous les droits sur tous les clubs, qu'il en soit membre
ou non. L'indicateur `is_administrator` est exclu de l'assignation de masse.

## Rôles de club

| Code        | Nom       | Résumé                                                               |
|-------------|-----------|----------------------------------------------------------------------|
| `PRESIDENT` | Président | Dirige le club : droits de l'animateur, plus la gestion de l'équipe. |
| `ANIMATOR`  | Animateur | Anime le club : séances, présences, joueurs, tournois, résultats.    |
| `PLAYER`    | Joueur    | Joue au club : classement, statistiques, tournois, résultats.        |

Président et animateur forment les **gestionnaires** du club (`Role::MANAGERS`) : ils accèdent au
mode « Gérer le club » (`ClubPolicy::manage()`).

### Président

Dispose de tous les droits de l'animateur, et en plus :

- ajoute des membres au club avec n'importe quel rôle : présidents, animateurs ou joueurs ;
- nomme et retire les animateurs et les autres présidents ;
- modifie et supprime le club (`ClubPolicy::update()`) ;
- personnalise les sections du club : renomme la section générale, en crée d'autres
  (« Section jeunes », « Section mardi »…) ;
- répartit les joueurs dans les sections (`ClubPolicy::manageSections()`). À l'ajout d'un joueur,
  il peut choisir sa section ; ce choix reste facultatif, et un joueur peut n'appartenir à
  aucune section.

Un club compte **un ou plusieurs présidents**, et **toujours au moins un** : le dernier président
ne peut être ni retiré du club ni changé de rôle. Pour partir, il nomme d'abord un autre président.

### Animateur

- Gère les séances et saisit les présences.
- Ajoute des joueurs au club, y compris des joueurs sans compte.
- Organise les tournois.
- Valide ou saisit les résultats des parties (voir « Saisie des résultats »).

### Joueur

- Rejoint un club avec son code d'invitation (`clubs.code`).
- Consulte le classement ELO du club ; son classement est unique tous clubs confondus
  (`users.elo_rating`).
- Consulte ses présences et ses statistiques.
- S'inscrit aux tournois du club.
- Saisit le résultat de ses parties.

## Création d'un club

Tout utilisateur connecté peut créer un club ; il en devient le premier président (`ClubForm`).
Il y ajoute ensuite les autres membres : présidents, animateurs ou joueurs.

## Saisie des résultats

Chacun des deux joueurs d'une partie peut saisir le résultat. Le résultat s'enregistre selon les
saisies reçues :

| Saisies des joueurs           | Résultat                                   |
|-------------------------------|--------------------------------------------|
| Deux saisies cohérentes       | Validé automatiquement.                    |
| Deux saisies contradictoires  | En attente : l'animateur tranche.          |
| Une seule saisie, ou aucune   | En attente : l'animateur valide ou saisit. |

## Matrice des droits

✅ : autorisé · ❌ : refusé · 🔸 : sous condition (voir la note)

| Action                                   | Administrateur | Président  | Animateur  | Joueur |
|------------------------------------------|:--------------:|:----------:|:----------:|:------:|
| Créer un club                            | ✅             | ✅         | ✅         | ✅     |
| Modifier / supprimer le club             | ✅ tous        | ✅ le sien | ❌         | ❌     |
| Ajouter / retirer présidents, animateurs | ✅ tous        | ✅ le sien | ❌         | ❌     |
| Créer / renommer les sections            | ✅ tous        | ✅ le sien | ❌         | ❌     |
| Répartir les joueurs dans les sections   | ✅ tous        | ✅ le sien | ❌         | ❌     |
| Gérer le club (tableau de bord)          | ✅ tous        | ✅ le sien | ✅ le sien | ❌     |
| Ajouter un joueur au club                | ✅             | ✅         | ✅         | ❌     |
| Gérer les séances et les présences       | ✅             | ✅         | ✅         | ❌     |
| Organiser un tournoi                     | ✅             | ✅         | ✅         | ❌     |
| S'inscrire à un tournoi                  | ❌             | ❌         | ❌         | ✅     |
| Saisir le résultat de sa partie          | ❌             | ❌         | ❌         | 🔸 (1) |
| Valider / saisir un résultat             | ✅             | ✅         | ✅         | ❌     |
| Consulter le classement du club          | ✅             | ✅         | ✅         | ✅     |
| Consulter ses présences / statistiques   | —              | —          | —          | ✅     |

(1) Le joueur doit avoir un compte et être connecté ; le résultat suit les règles de la section
« Saisie des résultats ».

## Reste à construire

Ces règles sont fixées ; les écrans correspondants restent à faire.

- **Gestion des membres** : l'ajout de membres n'inscrit aujourd'hui que des joueurs. Le choix du
  rôle (président, animateur), le changement de rôle et le retrait d'un membre restent à faire,
  avec la garde du dernier président.
- **Suppression d'un club**, réservée au président et à l'administrateur.
- **Gestion des sections** : création, renommage et répartition des joueurs déjà inscrits.
- **Liste des joueurs du club**, avec un filtre sur les joueurs sans section.
- **Saisie et validation des résultats**, **tournois**, **statistiques du joueur**.
- **Badges** : présidents et animateurs portent le même badge « prof » (« anim. » dans la barre
  latérale).
