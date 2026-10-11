# Clubs

Ce document décrit l'organisation d'un club : ses membres et ses sections. Les droits de chacun
figurent dans [roles.md](roles.md), les événements (cours, tournois) et le pointage dans
[events.md](events.md).

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
| Une section appartient à un seul club                  | `sections.club_id`, obligatoire        |
| Un club compte une à plusieurs sections                | section générale créée avec le club    |
| Noms uniques au sein d'un club                         | unicité `(name, club_id)`              |
| Un joueur appartient à zéro, une ou plusieurs sections | table `section_user`                   |
| Un membre tient un rôle dans chacune de ses sections   | `section_user.role_id`                 |

- **Section générale** : créée avec le club (`Club::createDefaultSection()`), nommée dans la
  langue courante (« Section générale », « General section »). Le président peut la renommer.
- **Rôle dans la section** : distinct du rôle au club (`club_user`), il permet par exemple
  d'attitrer un animateur à une section. Un joueur ajouté à une section y reçoit le rôle de
  joueur (`Section::players()`).
- **Répartition** : réservée au président et à l'administrateur
  (`ClubPolicy::manageSections()`). À l'ajout d'un joueur, le choix de sa section reste
  facultatif.
- **Suppression** : supprimer une section libère ses membres ; supprimer un club supprime ses
  sections, et supprimer un utilisateur le retire de ses sections.
- **Garde à construire** : rien n'empêche encore la suppression de la dernière section d'un club ;
  la règle sera appliquée par l'écran de gestion des sections.

## Modèle de données

```
clubs 1 ── n sections n ── n users           (section_user, avec role_id → roles)
clubs n ── n users                           (club_user, avec role_id → roles)
clubs 1 ── n events n ── n users             (attendances, avec role_id → roles, voir events.md)
```
