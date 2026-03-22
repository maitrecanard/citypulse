/# citypulse

## Description
Tu es un expert en développement SaaS PHP Laravel et front React, spécialisé dans le marketing digital et le marketing avec plus de 20 ans d'exp. CityPulse, est un système SaaS pour les villes et villages. Il permet la création et la gestion des doléance créé par les administré et pris en charge par les communes. il permet également aux agents communaux la gestion des évènement, 
annonces, les alertes de voisinage, ainsi que la gestion des interventions des agents de terrain.
C'est un service clé en main, complet, qui fonctionne en pwa sur tout support. Ainsi les administré peuvent facilement et simple communiqué avec leurs élu et les élu peuvent gérer faclilement l'ensemble des agents véhicule et intervention.

## Stack
- laravel vite monolitique
- react
- redis
- tailwind
- Discord
- Stripe

## SEO
Chaque ville enregistré possède une url dédié, permettant au administré de retrouver le site simplement, ainsi les moteurs de recherche peuvent également et éfficacement référencer le système
- Audit SEO complet permettant d'arriver à une note supérieur à 90/100

## Sécurié
- connexion sécurisé avec httponly
- ne jamais sortir les données sensible client à par pour la page profil de l'utilisateur
- Ne jamais utiliser l'id sur le front, utiliser dans les liens des uuid pour identifier les villes et les users
- un administré ne peux pas modifier une ville, il peut uniquement créer une doléance, la modifier, ou la supprimer si celle-ci n'est pas encore consulté par l'administration de la ville
- un administré peut modifier uniquement une doléance dont il est propriétaire
- un administré peut uniquement modifié son profil, son mot de passe, ses informations personnel, il ne peut en aucun cas modifier ceux d'une tièrce personne.
- idem pour les agents (maire, secrétaire, agent communaux) : un maire peux créer une secrétaire, une secrétaire responsable des ressources humaines peux créer des agents ou d'autre personnes pour des services, des services, des véhicule, des annonces, des événements, gérer les doléances utilisateur
- Audit complet de sécurité pour arriver à une note à plus de 90/100

## Gestion des véhicules
- gestion des véhicule par équipe
- gestion de l'entretien des véhicule avec système par mail et par notification

## charte graphique
J'exige une charte graphique moderne, dynamique (uniquement en CSS) attrayante, afin d'atteindre une audience de visibilité rentable, mais également un taux de convertion de plus de 80/100 sur les abonnements stripe.

## Exigence
- toute les icones doivent être fonctionnel
- toutes les icones doivent être fonctionnel
- mettre des logs discord partout dans lorsqu'il y a une erreur, mais également dasn les try
- toutes les fonctionnalité doivent faire l'object de tests unitaire strict, aucun build aucun commit si le code n'est pas impécable, conforme fonctionnel, prêt à l'emploi

## Abonnement
Abonnement à tarif unique de 80€ par mois sans engagement.
