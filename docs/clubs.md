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
- **Président** : un club en compte toujours au moins un. La garde sera appliquée par l'écran
  de gestion des membres, qui reste à construire.
- **Joueur sans compte** : créé par un président ou un animateur, sans e-mail ni mot de passe. Il
  figure au club et au classement, sans pouvoir se connecter.
- **Classement ELO** : propre au joueur et commun à tous ses clubs (`users.elo_rating`, 1200 au
  départ). La recherche d'un joueur existant avant d'en créer un nouveau évite les doublons d'ELO.

## Modèle de données

```
clubs n ── n users                           (memberships, avec role_id → roles et joined_at)
clubs 1 ── n events n ── n users             (attendances, avec role_id → roles, voir events.md)
```
