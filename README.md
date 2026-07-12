# TCG-PCM

Jeu de cartes à collectionner (TCG) en temps réel, développé dans le cadre du projet annuel ESGI.
Backend Symfony 8 / API Platform (event-sourcing + randomness seedé pour le rejeu déterministe des parties) et frontend Next.js 16 / React 19, connectés en temps réel via Mercure.

## Équipe

| Nom | Handle |
| --- | --- |
| Wiatr Maxime | [@Naegato](https://github.com/Naegato) |
| Ambroise Pierre | [@fan2shrek](https://github.com/fan2shrek) |
| Masson Charlie | [@lemonkyy](https://github.com/lemonkyy) |

La répartition des tâches (moteur de jeu, API, frontend, infra/CI) est répartie entre les membres au fil des sprints ; voir l'historique Git pour le détail des contributions.

## Architecture

- **`api/`** — Symfony 8 + API Platform. Moteur de jeu event-sourcé (`GamePipeline` → middlewares → `GameEventApplier`), persistance Doctrine/Redis, temps réel via Mercure, auth JWT (LexikJWTAuthenticationBundle).
- **`front/`** — Next.js (App Router) + React 19. Deux zones principales : menu/lobby (`app/(app)`) et écran de jeu (`app/(game)/game`), drag & drop pour la main/zone de jeu, abonnement Mercure côté client.
- **`infra/`** — scripts d'infrastructure (backup DB, etc.).
- **`stack.yml` / `stack.backup.yml` / `stack.infisical.yml`** — déploiement Docker Swarm (prod) : API + front répliqués, MariaDB, Redis, Mercure, Dozzle (supervision des conteneurs), Infisical (secrets), backup automatisé.
- **`compose.yaml`** — environnement de développement local (php, front, db, phpMyAdmin, redis, mercure).

## Prérequis

- Docker + Docker Compose

## Installation locale

```bash
make up
```

Cette commande build/démarre les conteneurs, installe les dépendances Composer, attend la disponibilité de la base de données, génère les clés JWT et synchronise le schéma.

- Frontend : http://localhost:3000
- API : http://localhost:8000/api (doc API Platform sur `/api/docs`)
- phpMyAdmin : http://localhost:8090
- Mercure hub : http://localhost:8080/.well-known/mercure
- Mailpit (emails envoyés en dev, ex. réinitialisation de mot de passe) : http://localhost:8025

Autres commandes utiles (voir la `makefile` pour la liste complète) :

```bash
make fixtures       # charge les fixtures Doctrine
make dbReset        # reset complet de la base dev (env=test pour la base de test)
make card-list       # régénère la map id -> classe des cartes
make tests           # suite PHPUnit complète
make tests-replay     # rejoue les parties enregistrées (garde-fou du moteur de jeu)
make lint / make format  # qualité de code API (Mago)
```

Le frontend tourne uniquement via Docker (`make up`) ; lint : `docker compose exec front npm run lint`.

## Déploiement (production)

Déploiement en Docker Swarm sur un VPS, via les stacks `stack.yml` (services applicatifs, répliqués ≥2 hors base de données), `stack.infisical.yml` (gestion des secrets) et `stack.backup.yml` (sauvegarde automatisée de la base de données). Les images sont publiées sur GitHub Container Registry (voir `.github/workflows/docker.yml`).

## CI

Workflows GitHub Actions (`.github/workflows/`) : qualité de code API (Mago lint/format/stan), vérifications de code, build/push des images Docker, métriques de tests.

## Roadmap

- [x] Mot de passe oublié / réinitialisation
- [x] Politique de mot de passe fort + renouvellement périodique
- [x] Blocage après tentatives de connexion infructueuses
- [ ] Authentification avancée (OAuth2, lien magique) + 2FA
- [ ] Monitoring erreurs (Sentry/GlitchTip) et analytique
- [x] Pages légales (CGU/CGV/Contact) et bandeau cookies
- [ ] Tests frontend
- [x] Nom de domaine + certificat SSL en prod
