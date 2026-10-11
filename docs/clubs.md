# Clubs

Ce document décrit l'organisation d'un club : ses membres et leurs adhésions. Les droits de chacun
figurent dans [roles.md](roles.md), les événements (cours, tournois), leurs participants et le
pointage dans [events.md](events.md).

## Club

Un club réunit des membres autour des échecs, le plus souvent dans un établissement scolaire.

- **Création** : tout utilisateur connecté crée un club et en devient le premier président
  (`ClubForm::create()`).
- **Code d'invitation** : unique, formé des initiales du nom et de quatre chiffres
  (« Échiquier Nord » → `EN-1177`, `Club::generateCode()`). Les élèves rejoignent le club avec
  ce code.
- **Informations facultatives** : ville, description (jour, heure et lieu des séances), logo.
- **Créateur** : conservé dans `clubs.user_id` ; un utilisateur qui a créé un club ne peut pas
  être supprimé.

## Membres

Un membre est un utilisateur inscrit au club avec **un seul rôle** : président, animateur, arbitre
ou joueur (voir [roles.md](roles.md)).

- **Adhésion** : l'inscription d'un utilisateur à un club (`memberships`, modèle `Membership`),
  avec son rôle et sa date d'adhésion (`joined_at`, une date sans heure). Elle porte son propre
  identifiant ; un utilisateur adhère au plus une fois à chaque club (unicité
  `(club_id, user_id)`). La date d'adhésion vaut par défaut le jour de l'inscription et peut être
  antérieure, pour un membre arrivé au club avant l'application.
- **Statut de l'adhésion** : porté par `memberships.membership_status_id`
  (`membership_statuses`, constantes de `MembershipStatus`), historisé (voir la section
  « Historique des statuts »).

| Statut     | Sens                                                        | Membre du club |
|------------|-------------------------------------------------------------|----------------|
| En attente | demande d'adhésion par le code, à valider par un gestionnaire | non          |
| Actif      | membre à part entière                                       | oui            |
| Suspendu   | membre temporairement écarté                                | non            |
| Parti      | ancien membre ; adhésion et historique conservés            | non            |

- **Membres** : seules les adhésions actives font les membres d'un club (`Club::members()`,
  `User::clubs()`) ; les droits, le pointage, les statistiques et le choix des participants en
  découlent. `Club::memberships()` donne toutes les adhésions, quel que soit leur statut.
- **Admission** : ajouter un membre crée son adhésion, active (`Club::admit()`) ; un ancien
  membre (en attente, suspendu ou parti) retrouve son adhésion, réactivée avec son rôle.
- **Président** : un club en compte toujours au moins un. La garde sera appliquée par l'écran
  de gestion des membres, qui reste à construire.
- **Joueur sans compte** : créé par un président ou un animateur, sans e-mail ni mot de passe. Il
  figure au club et au classement, sans pouvoir se connecter.
- **Classement ELO** : propre au joueur et commun à tous ses clubs (`users.elo_rating`, 1200 au
  départ). La recherche d'un joueur existant avant d'en créer un nouveau évite les doublons d'ELO.

## Historique des statuts

Les statuts d'adhésion, d'événement et de présence s'historisent de la même façon
(`App\Models\Concerns\RecordsStatusChanges`) : la création puis chaque changement de statut
ajoutent une ligne à la table d'historique, avec le statut pris, sa date et son auteur.

| Statut courant                      | Historique                                         |
|-------------------------------------|----------------------------------------------------|
| `memberships.membership_status_id`  | `membership_status_changes` (date sans heure)      |
| `events.event_status_id`            | `event_status_changes` (horodatage, voir events.md) |
| `attendances.attendance_status_id`  | `attendance_status_changes` (horodatage, voir events.md) |

- **Statut courant** : il reste sur l'enregistrement lui-même, pour les requêtes courantes ;
  l'historique en garde chaque valeur, et ses lignes ne se modifient pas.
- **Changement** : `changeStatus()` change le statut et l'historise ; garder le même statut
  n'ajoute rien. Une date antérieure reprend l'historique d'un club existant ; l'adhésion inscrit
  son statut initial à sa date d'adhésion.
- **Auteur** : l'utilisateur connecté ; à défaut (seeders, console), le créateur du club.
- **Suppression** : supprimer une adhésion, un événement ou un pointage supprime son historique.

## Modèle de données

```
clubs n ── n users                           (memberships : role_id → roles, membership_status_id, joined_at)
memberships 1 ── n membership_status_changes
clubs 1 ── n events n ── n users             (attendances : role_id → roles, attendance_status_id, voir events.md)
```
