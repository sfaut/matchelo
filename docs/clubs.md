# Clubs

Ce document décrit l'organisation d'un club : ses membres, ses sections et ses séances. Les droits
de chacun figurent dans [roles.md](roles.md).

## Club

Un club réunit des membres autour des échecs, le plus souvent dans un établissement scolaire.

- **Création** : tout utilisateur connecté crée un club et en devient le premier président
  (`ClubForm::create()`). Le club reçoit en même temps sa section générale.
- **Code d'invitation** : unique, formé des initiales du nom et de quatre chiffres
  (« Échiquier Nord » → `EN-1177`, `Club::generateCode()`). Les élèves rejoignent le club avec
  ce code.
- **Informations facultatives** : ville, description (jour, heure et lieu des séances), logo.
- **Créateur** : conservé dans `clubs.user_id` ; un utilisateur qui a créé un club ne peut pas
  être supprimé.

## Membres

Un membre est un utilisateur inscrit au club avec **un seul rôle** : président, animateur ou
joueur (table `club_user`, voir [roles.md](roles.md)).

- **Président** : un club en compte toujours au moins un. La garde sera appliquée par l'écran
  de gestion des membres, qui reste à construire.
- **Joueur sans compte** : créé par un président ou un animateur, sans e-mail ni mot de passe. Il
  figure au club et au classement, sans pouvoir se connecter.
- **Classement ELO** : propre au joueur et commun à tous ses clubs (`users.elo_rating`, 1200 au
  départ). La recherche d'un joueur existant avant d'en créer un nouveau évite les doublons d'ELO.

## Sections

Une section regroupe une partie des joueurs d'un club, par âge, niveau ou créneau
(« Section jeunes », « Section vétérans », « Section mardi »).

| Règle                                                  | Mise en œuvre                          |
|--------------------------------------------------------|----------------------------------------|
| Une section appartient à un seul club                  | `club_sections.club_id`, obligatoire   |
| Un club compte une à plusieurs sections                | section générale créée avec le club    |
| Noms uniques au sein d'un club                         | unicité `(club_id, name)`              |
| Un joueur appartient à zéro, une ou plusieurs sections | table `club_section_user`              |

- **Section générale** : créée avec le club (`Club::createDefaultSection()`), nommée dans la
  langue courante (« Section générale », « General section »). Le président peut la renommer.
- **Répartition** : réservée au président et à l'administrateur
  (`ClubPolicy::manageSections()`). À l'ajout d'un joueur, le choix de sa section reste
  facultatif.
- **Suppression** : supprimer une section libère ses joueurs ; un club qui a encore des sections
  ne peut pas être supprimé directement.
- **Garde à construire** : rien n'empêche encore la suppression de la dernière section d'un club ;
  la règle sera appliquée par l'écran de gestion des sections.

## Séances et présences

- **Séance** : horodatée à la seconde ; deux séances d'un même club ne partagent pas le même
  horodatage (`club_sessions`).
- **Présence** : un membre pointé présent à une séance (`attendances`) ; supprimer une séance
  supprime ses présences.

## Modèle de données

```
clubs 1 ── n club_sections n ── n users      (club_section_user)
clubs n ── n users                           (club_user, avec role_id → roles)
clubs 1 ── n club_sessions n ── n users      (attendances)
```
