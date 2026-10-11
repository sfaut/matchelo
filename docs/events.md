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

Le pointage enregistre une ligne par membre pointé (`attendances`), avec son statut de présence :
présent, absent ou excusé (`attendance_statuses`, constantes de `AttendanceStatus`).

- **Écran de pointage** : pointer une séance crée au besoin un **cours terminé** à cette date
  (`Club::createCourse()`). Les membres pointés reçoivent le statut présent
  (`Event::attendees()`) ; les absents n'ont pas encore de ligne.
- **Statistiques** : taux de présence et assiduité se calculent sur les présents
  (`AttendanceReport`), tous types d'événement confondus.

## Reste à construire

- Création et planification des événements (tournois notamment), avec leur statut.
- Pointage des absents et des excusés.
- Clôture du pointage (statut clôturé) et limitation des statistiques aux cours.
