# Événements

Un événement réunit les membres d'un club à une date : cours, tournoi ou autre. Le pointage y
enregistre la présence de chacun. L'organisation du club figure dans [clubs.md](clubs.md).

## Événement

- **Horodatage** : à la seconde (`events.scheduled_at`). Un club peut tenir plusieurs événements
  au même horodatage, par exemple un cours et un tournoi.
- **Type** : cours, tournoi ou autre (`event_types`, constantes de `EventType`).
- **Statut** : suit la vie de l'événement (`event_statuses`, constantes de `EventStatus`),
  historisé dans `event_status_changes` (voir la section « Historique des statuts » de
  [clubs.md](clubs.md)).

| Statut      | Sens                                               |
|-------------|----------------------------------------------------|
| Brouillon   | en cours de création, non visible                  |
| Planifié    | confirmé et planifié                               |
| En cours    | en cours de réalisation                            |
| Terminé     | terminé, pointage encore modifiable                |
| Clôturé     | terminé, pointage définitivement clos              |
| Annulé      | annulé                                             |

- **Suppression** : supprimer un événement supprime son pointage ; un club qui a des événements
  ne peut pas être supprimé directement.

## Ordre d'affichage

Les statuts d'événement et de présence portent un ordre d'affichage (`display_order`), utilisé
par les menus de choix (portée `ordered()`). Il est valorisé par dizaines (10, 20, 30…) : on
insère un statut entre deux autres sans renuméroter (25 entre 20 et 30). Les tableaux de ce
document suivent cet ordre.

## Rôles à l'événement

Chaque participant tient un rôle à l'événement (`attendances.role_id`), pris parmi les rôles de
club (`roles`, constantes de `Role`, voir [roles.md](roles.md)) :

| Rôle      | À l'événement                              |
|-----------|--------------------------------------------|
| Animateur | anime le cours, encadre le tournoi         |
| Joueur    | reçoit le cours (élève), joue au tournoi   |
| Arbitre   | arbitre les parties                        |

Le président n'est pas un rôle d'événement : un président qui participe y anime.

- **Rôle par défaut** : le rôle de club donne le rôle à l'événement
  (`Role::EVENT_ROLE_BY_CLUB_ROLE`) ; un participant extérieur au club y joue. Le rôle à
  l'événement reste propre à chaque événement : un arbitre peut animer un cours, un animateur y
  suivre l'enseignement d'un autre.
- **Choix des participants** : pour chaque rôle, tous les membres du club sont proposés, d'abord
  ceux dont le rôle de club y conduit (présidents et animateurs pour animer), puis les autres
  (`Club::candidatesFor()`).
- **Pointage** : pointer un participant ne change pas son rôle ; un membre pointé sans être
  inscrit reçoit le rôle par défaut.

## Planification en masse

Les événements se planifient et se composent en masse (`App\Support\EventPlanner`).

**Décision : des événements indépendants.** Chaque événement généré vit sa propre vie, détaché
des événements générés avec lui ; le modèle ne connaît pas de série. Les occurrences d'un créneau divergent vite
(vacances, annulation, animateur remplacé, cours déplacé), et chaque écart serait une exception à
gérer dans une série. Les opérations en masse portent donc sur une **sélection d'événements**, que
l'écran filtrera (période, jour, heure, type). Une série s'ajoutera plus tard si une modification
doit un jour se propager d'elle-même aux occurrences à venir.

| Opération                | Effet                                                              |
|--------------------------|--------------------------------------------------------------------|
| `occurrences()`          | horodatages des jours choisis sur une période : aperçu avant génération, pour retirer des dates |
| `generate()`             | un événement planifié par horodatage, avec ses animateurs et joueurs attendus |
| `update()`               | heure (date conservée), statut, description ; un champ omis reste en place |
| `delete()`               | supprime les événements et leur pointage                          |
| `enrol()`                | inscrit des membres avec un rôle ; un membre déjà inscrit change de rôle et garde son statut de présence |
| `withdraw()`             | retire des membres, avec leur pointage                            |
| `copyParticipants()`     | reprend les participants d'un événement, avec leur rôle (groupe du mardi, des jeunes…) |

- **Droits** : réservés aux présidents du club et aux administrateurs
  (`ClubPolicy::planEvents()`, voir [roles.md](roles.md)). `EventPlanner` n'en vérifie aucun :
  l'écran qui l'appelle les contrôle.
- **Portée** : chaque opération porte sur des événements du club ; un événement d'un autre club
  fait échouer l'opération entière.
- **Atomicité** : chaque opération s'exécute en une transaction ; un échec n'en laisse rien.
- **Cours complet** : un cours compte **au moins un animateur et au moins un joueur**
  (`Event::isStaffed()`). Une génération ou un changement de composition qui laisserait un cours
  sans l'un ou l'autre est refusé. Les autres types d'événement n'y sont pas tenus : un tournoi
  peut se passer d'animateur.
- **Événement clôturé** : son pointage est définitivement clos, sa composition ne change plus.

## Pointage

Le pointage enregistre une ligne par membre et par événement (`attendances`, modèle
`Attendance`), avec son propre identifiant, son rôle à l'événement et son statut de présence
(`attendance_statuses`, constantes de `AttendanceStatus`). Un utilisateur est pointé au plus une
fois par événement (unicité `(event_id, user_id)`). Chaque statut de présence pris s'historise
dans `attendance_status_changes`, horodaté et attribué à son auteur (voir la section
« Historique des statuts » de [clubs.md](clubs.md)).

| Statut    | Sens                                          | Compté présent |
|-----------|-----------------------------------------------|----------------|
| Prévu     | joueur attendu, pas encore pointé             | non            |
| Présent   | joueur présent                                | oui            |
| Absent    | joueur absent non excusé                      | non            |
| Excusé    | joueur absent avec excuse acceptée            | non            |
| En retard | joueur arrivé en retard                       | oui            |

- **Événement planifié** : ses participants y sont inscrits au statut prévu
  (voir la section « Planification en masse »).
- **Cours créé par le pointage** : chaque membre du club y est inscrit au statut prévu, avec son
  rôle par défaut (`Event::expectMembers()`).
- **Écran de pointage** : pointer une séance crée au besoin un **cours terminé** à cette date
  (`Club::createCourse()`). Pointer un membre le passe à présent ; le dépointer, ou remettre
  l'appel à zéro, le ramène à prévu.
- **Statistiques** : taux de présence et assiduité comptent les statuts présent et en retard
  (`AttendanceStatus::ATTENDING`), tous types d'événement confondus (`AttendanceReport`).

## Reste à construire

- **Écrans de planification** : génération avec aperçu des dates, sélection filtrée des
  événements, opérations en masse et composition, sur `EventPlanner` et `Club::candidatesFor()`.
- Pointage des statuts absent, excusé et en retard depuis l'écran de pointage.
- Clôture du pointage (statut clôturé) et limitation des statistiques aux cours.
