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

## Pointage

Le pointage enregistre une ligne par membre et par événement (`attendances`), avec son statut de
présence (`attendance_statuses`, constantes de `AttendanceStatus`).

| Statut    | Sens                                          | Compté présent |
|-----------|-----------------------------------------------|----------------|
| Prévu     | joueur attendu, pas encore pointé             | non            |
| Présent   | joueur présent                                | oui            |
| Absent    | joueur absent non excusé                      | non            |
| Excusé    | joueur absent avec excuse acceptée            | non            |
| En retard | joueur arrivé en retard                       | oui            |
| Blessé    | joueur présent mais blessé, ne participe pas  | oui            |

- **Création de l'événement** : chaque membre du club y est inscrit au statut prévu
  (`Event::expectMembers()`).
- **Écran de pointage** : pointer une séance crée au besoin un **cours terminé** à cette date
  (`Club::createCourse()`). Pointer un membre le passe à présent ; le dépointer, ou remettre
  l'appel à zéro, le ramène à prévu.
- **Statistiques** : taux de présence et assiduité comptent les statuts présent, en retard et
  blessé (`AttendanceStatus::ATTENDING`), tous types d'événement confondus (`AttendanceReport`).

## Reste à construire

- Création et planification des événements (tournois notamment), avec leur statut.
- Pointage des statuts absent, excusé, en retard et blessé depuis l'écran de pointage.
- Clôture du pointage (statut clôturé) et limitation des statistiques aux cours.
