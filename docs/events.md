# Événements

Un événement réunit les membres d'un club à une date : cours, tournoi ou autre. Le pointage y
enregistre la présence de chacun. L'organisation du club figure dans [clubs.md](clubs.md).

## Événement

- **Horodatage** : à la seconde (`events.scheduled_at`). Un club peut tenir plusieurs événements
  au même horodatage, par exemple un cours et un tournoi.
- **Type** : cours, tournoi ou autre (`event_types`, constantes de `EventType`).
- **Statut** : suit la vie de l'événement (`event_statuses`, constantes de `EventStatus`).

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
| Animateur | anime le cours, organise le tournoi        |
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

## Pointage

Le pointage enregistre une ligne par membre et par événement (`attendances`), avec son rôle à
l'événement et son statut de présence (`attendance_statuses`, constantes de `AttendanceStatus`).

| Statut    | Sens                                          | Compté présent |
|-----------|-----------------------------------------------|----------------|
| Prévu     | joueur attendu, pas encore pointé             | non            |
| Présent   | joueur présent                                | oui            |
| Absent    | joueur absent non excusé                      | non            |
| Excusé    | joueur absent avec excuse acceptée            | non            |
| En retard | joueur arrivé en retard                       | oui            |

- **Planification d'un cours** : un cours planifié compte **au moins un animateur et au moins un
  joueur**, inscrits au statut prévu (`Club::scheduleCourse()`).
- **Cours créé par le pointage** : chaque membre du club y est inscrit au statut prévu, avec son
  rôle par défaut (`Event::expectMembers()`).
- **Écran de pointage** : pointer une séance crée au besoin un **cours terminé** à cette date
  (`Club::createCourse()`). Pointer un membre le passe à présent ; le dépointer, ou remettre
  l'appel à zéro, le ramène à prévu.
- **Statistiques** : taux de présence et assiduité comptent les statuts présent et en retard
  (`AttendanceStatus::ATTENDING`), tous types d'événement confondus (`AttendanceReport`).

## Reste à construire

- Création et planification des événements (tournois notamment), avec leur statut ; l'écran de
  planification d'un cours (choix des animateurs et des joueurs) s'appuiera sur
  `Club::scheduleCourse()` et `Club::candidatesFor()`.
- Pointage des statuts absent, excusé et en retard depuis l'écran de pointage.
- Clôture du pointage (statut clôturé) et limitation des statistiques aux cours.
