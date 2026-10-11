# Rôles utilisateur

Ce document décrit les rôles métier de Matchelo et les droits qui en découlent. Le code reste la
référence pour les valeurs (codes, identifiants) : ce fichier y renvoie sans les recopier.

## Principe

Matchelo distingue deux niveaux :

- **l'administrateur**, global à l'application, signalé par l'indicateur `users.is_administrator` ;
- **les rôles de club** (président, animateur, arbitre, joueur), portés par les adhésions
  (`memberships.role_id` → `roles.id`, modèle `Membership`). Seule une adhésion active donne
  des droits : un membre en attente, suspendu ou parti n'en a aucun dans le club (voir
  [clubs.md](clubs.md)).

L'unicité `(club_id, user_id)` des adhésions limite chaque utilisateur à **un seul rôle
par club** ; il peut tenir des rôles différents dans des clubs différents (animateur ici, joueur
là).

Le rôle de club est le rôle premier du membre. Il donne son rôle par défaut à chaque événement,
où il peut en tenir un autre (voir [events.md](events.md)). Les droits décrits ici découlent du
seul rôle de club.

**Seuls les utilisateurs connectés agissent dans l'application**, ce qui suppose un compte avec
e-mail. Un joueur sans compte (`users.email` à `NULL`) figure dans son club et au classement,
mais n'interagit pas : l'animateur agit pour lui.

Références dans le code :

- table `roles` et indicateur `is_administrator` :
  `database/migrations/0001_01_01_000000_create_users_table.php` ;
- valeurs des rôles : `database/seeders/RoleSeeder.php` ;
- constantes et recherche par code : `App\Models\User\Role` (`Role::PRESIDENT`, `Role::ANIMATOR`,
  `Role::REFEREE`, `Role::PLAYER`, `Role::MANAGERS`, `Role::idFor()`, `Role::idsFor()`) ;
- rôles d'un utilisateur : `User::managedClubs()`, `User::isManager()`, `User::presides()` ;
- droits sur les clubs : `App\Policies\ClubPolicy`.

## Administrateur

Administre l'application. Il dispose de tous les droits sur tous les clubs, qu'il en soit membre
ou non. L'indicateur `is_administrator` est exclu de l'assignation de masse.

## Rôles de club

| Code        | Nom       | Résumé                                                               |
|-------------|-----------|----------------------------------------------------------------------|
| `PRESIDENT` | Président | Dirige le club : droits de l'animateur, plus l'équipe et les tournois. |
| `ANIMATOR`  | Animateur | Anime le club : séances, présences, joueurs, résultats.              |
| `REFEREE`   | Arbitre   | Arbitre les parties : valide ou saisit les résultats.                |
| `PLAYER`    | Joueur    | Joue au club : classement, statistiques, tournois, résultats.        |

Président et animateur forment les **gestionnaires** du club (`Role::MANAGERS`) : ils accèdent au
mode « Gérer le club » (`ClubPolicy::manage()`). L'arbitre n'en fait pas partie.

### Président

Dispose de tous les droits de l'animateur, et en plus :

- ajoute des membres au club avec n'importe quel rôle : présidents, animateurs, arbitres ou
  joueurs ;
- nomme et retire les animateurs, les arbitres et les autres présidents ;
- modifie et supprime le club (`ClubPolicy::update()`) ;
- organise les tournois du club : l'animateur peut y animer, sans pouvoir en créer ;
- planifie les événements du club en masse : génération, modification, suppression et
  composition (`ClubPolicy::planEvents()`, voir [events.md](events.md)).

Un club compte **un ou plusieurs présidents**, et **toujours au moins un** : le dernier président
ne peut être ni retiré du club ni changé de rôle. Pour partir, il nomme d'abord un autre président.

### Animateur

- Gère les séances et saisit les présences.
- Ajoute des joueurs au club, y compris des joueurs sans compte.
- Valide ou saisit les résultats des parties (voir « Saisie des résultats »).

### Arbitre

Arbitre les parties du club, sans le gérer :

- valide ou saisit les résultats des parties, et tranche les saisies contradictoires
  (voir la section « Saisie des résultats ») ;
- consulte le classement du club.

Il n'accède pas au mode « Gérer le club » : ni séances, ni présences, ni joueurs, ni tournois.
Un seul rôle par club : l'arbitre ne joue pas dans ce club (il peut être joueur dans un autre).

### Joueur

- Rejoint un club avec son code d'invitation (`clubs.code`).
- Consulte le classement ELO du club ; son classement est unique tous clubs confondus
  (`users.elo_rating`).
- Consulte ses présences et ses statistiques.
- S'inscrit aux tournois du club.
- Saisit le résultat de ses parties.

## Création d'un club

Tout utilisateur connecté peut créer un club ; il en devient le premier président (`ClubForm`).
Il y ajoute ensuite les autres membres : présidents, animateurs, arbitres ou joueurs.

## Saisie des résultats

Chacun des deux joueurs d'une partie peut saisir le résultat. Le résultat s'enregistre selon les
saisies reçues :

| Saisies des joueurs           | Résultat                                                |
|-------------------------------|---------------------------------------------------------|
| Deux saisies cohérentes       | Validé automatiquement.                                 |
| Deux saisies contradictoires  | En attente : l'animateur ou l'arbitre tranche.          |
| Une seule saisie, ou aucune   | En attente : l'animateur ou l'arbitre valide ou saisit. |

## Matrice des droits

✅ : autorisé · ❌ : refusé · 🔸 : sous condition (voir la note)

| Action                                   | Administrateur | Président  | Animateur  | Arbitre | Joueur |
|------------------------------------------|:--------------:|:----------:|:----------:|:-------:|:------:|
| Créer un club                            | ✅             | ✅         | ✅         | ✅      | ✅     |
| Modifier / supprimer le club             | ✅ tous        | ✅ le sien | ❌         | ❌      | ❌     |
| Ajouter / retirer prés., anim., arbitres | ✅ tous        | ✅ le sien | ❌         | ❌      | ❌     |
| Gérer le club (tableau de bord)          | ✅ tous        | ✅ le sien | ✅ le sien | ❌      | ❌     |
| Planifier les événements en masse        | ✅ tous        | ✅ le sien | ❌         | ❌      | ❌     |
| Ajouter un joueur au club                | ✅             | ✅         | ✅         | ❌      | ❌     |
| Gérer les séances et les présences       | ✅             | ✅         | ✅         | ❌      | ❌     |
| Organiser un tournoi                     | ✅ tous        | ✅ le sien | ❌         | ❌      | ❌     |
| S'inscrire à un tournoi                  | ❌             | ❌         | ❌         | ❌      | ✅     |
| Saisir le résultat de sa partie          | ❌             | ❌         | ❌         | ❌      | 🔸 (1) |
| Valider / saisir un résultat             | ✅             | ✅         | ✅         | ✅      | ❌     |
| Consulter le classement du club          | ✅             | ✅         | ✅         | ✅      | ✅     |
| Consulter ses présences / statistiques   | —              | —          | —          | —       | ✅     |

(1) Le joueur doit avoir un compte et être connecté ; le résultat suit les règles de la section
« Saisie des résultats ».

## Reste à construire

Ces règles sont fixées ; les écrans correspondants restent à faire.

- **Gestion des membres** : l'ajout de membres n'inscrit aujourd'hui que des joueurs. Le choix du
  rôle (président, animateur), le changement de rôle et le retrait d'un membre restent à faire,
  avec la garde du dernier président.
- **Suppression d'un club**, réservée au président et à l'administrateur.
- **Liste des joueurs du club**.
- **Saisie et validation des résultats**, **tournois**, **statistiques du joueur**.
- **Badges** : présidents et animateurs portent le même badge « prof » (« anim. » dans la barre
  latérale).
