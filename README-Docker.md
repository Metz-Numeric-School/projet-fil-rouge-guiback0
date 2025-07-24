# Access MNS - Configuration Docker Compose

Cette configuration docker-compose orchestre la pile complète de l'application Access MNS incluant :

- **Frontend** : Application Angular (access_mns_client)
- **Backend** : Application Symfony (access_mns_manager)
- **Base de données** : PostgreSQL
- **Proxy** : Proxy inverse Nginx

## Prérequis

- Docker et Docker Compose installés
- Git (pour cloner le dépôt)
- Des fichiers `.env` configuré (voir `.env.example` pour le modèle)

## Démarrage Rapide

1. **Cloner le dépôt et naviguer vers la racine du projet :**

   ```bash
   # Cloner le dépôt depuis GitHub
   git clone https://github.com/Metz-Numeric-School/projet-fil-rouge-guiback0.git
   
   # Naviguer vers la racine du projet
   cd projet-fil-rouge-guiback0
   ```

2. **Configurer les variables d'environnement :**

   **Configuration globale du projet :**

   ```bash
   # Copier le fichier d'exemple vers .env (racine du projet)
   copy .env.example .env
   ```

   **Configuration spécifique au backend Symfony :**

   ```bash
   # Naviguer vers le dossier backend
   cd access_mns_manager

   # Copier le fichier d'exemple vers .env (backend)
   copy .env.example .env

   # Retourner à la racine du projet
   cd ..
   ```

   Ensuite, éditer les fichiers `.env` pour personnaliser votre configuration selon vos besoins :

   - **`.env` (racine)** : Configuration Docker Compose et variables globales
   - **`access_mns_manager/.env`** : Configuration spécifique à l'application Symfony (base de données, JWT, etc.)

   Les fichiers `.env.example` contiennent toutes les variables nécessaires avec des valeurs par défaut qui fonctionnent pour le développement.

3. **Construire et démarrer tous les services :**

   ```bash
   docker-compose up --build
   ```

4. **Accéder aux applications :**
   - **Frontend (Angular)** : http://localhost (via proxy) ou http://localhost:4200 (direct)
   - **Manager Backend** : http://localhost/manager/
   - **API Backend** : http://localhost/api/
   - **Backend (Direct)** : http://localhost:8000
   - **Base de données** : localhost:5432

## Services

### Frontend (access_mns_client)

- **Port** : 4200 (configurable via `CLIENT_PORT`)
- **Technologie** : Angular avec Nginx
- **Construction** : Build Docker multi-étapes avec Node.js + Nginx

### Backend (access_mns_manager)

- **Port** : 8000 (configurable via `MANAGER_PORT`)
- **Technologie** : Symfony avec FrankenPHP
- **Fonctionnalités** : Hub Mercure pour les fonctionnalités temps réel
- **Mode développement** : Inclut le support Xdebug

### Base de données

- **Port** : 5432
- **Technologie** : PostgreSQL 16
- **Identifiants** : Configurés via les variables d'environnement

### Proxy (Nginx)

- **Port** : 80 (HTTP), 443 (HTTPS)
- **Technologie** : Proxy inverse Nginx
- **Objectif** : Route le trafic entre les services frontend et backend
- **Routage** :
  - `/manager/*` → Application Symfony backend
  - `/api/*` → Points d'accès API backend
  - `/assets/*` → Ressources statiques backend
  - `/.well-known/mercure` → Hub Mercure
  - `/*` → Application Angular frontend (catch-all)

## Commandes

### Démarrer les services

```bash
# Démarrer tous les services
docker-compose up

# Démarrer en arrière-plan
docker-compose up -d

# Construire et démarrer
docker-compose up --build
```

### Arrêter les services

```bash
# Arrêter tous les services
docker-compose down

# Arrêter et supprimer les volumes
docker-compose down -v
```

### Logs

```bash
# Voir tous les logs
docker-compose logs

# Voir les logs d'un service spécifique
docker-compose logs frontend
docker-compose logs backend
docker-compose logs database
```

### Exécuter des commandes dans les conteneurs

```bash
# Accéder au conteneur backend
docker-compose exec backend bash

# Exécuter des commandes Symfony
docker-compose exec backend php bin/console cache:clear
docker-compose exec backend php bin/console doctrine:migrations:migrate

# Générer des clés/secrets sécurisés
docker-compose exec backend php -r "echo bin2hex(random_bytes(32));"

# Accéder à la base de données
docker-compose exec database psql -U access_mns_user -d access_mns

# Vérifier la configuration Symfony
docker-compose exec backend php bin/console debug:config
docker-compose exec backend php bin/console debug:router
```

## Utilisation de l'Application

### Accéder au Manager Backend

Le manager backend est accessible à `http://localhost/manager/`. C'est l'interface d'administration basée sur Symfony où vous pouvez :

- Gérer les utilisateurs et permissions
- Configurer les paramètres de l'application
- Surveiller les activités système
- Accéder aux outils d'administration

**URL de connexion** : `http://localhost/manager/login`

### Utiliser l'API

L'API REST est disponible à `http://localhost/api/`. Tous les points d'accès API sont préfixés par `/api/` :

- `GET /api/users` - Lister les utilisateurs
- `POST /api/users` - Créer un utilisateur
- `GET /api/users/{id}` - Obtenir les détails d'un utilisateur
- etc.

### Application Frontend

L'application Angular principale est servie à `http://localhost/` et fournit l'interface utilisateur du système.

## Développement

### Variables d'environnement

Le projet utilise plusieurs fichiers de configuration d'environnement :

**Variables globales (fichier `.env` à la racine) :**

- `CLIENT_PORT` : Port frontend (défaut : 4200)
- `MANAGER_PORT` : Port backend (défaut : 8000)
- `POSTGRES_*` : Configuration base de données PostgreSQL pour Docker

**Variables backend Symfony (fichier `access_mns_manager/.env`) :**

- `APP_ENV` : Environnement Symfony (dev/prod)
- `APP_SECRET` : Secret d'application Symfony (générer une clé sécurisée)
- `DATABASE_URL` : URL de connexion à la base de données
- `JWT_SECRET_KEY` / `JWT_PUBLIC_KEY` : Clés pour l'authentification JWT
- `JWT_PASSPHRASE` : Phrase de passe pour les clés JWT
- `MAILER_DSN` : Configuration du service de mail
- `TRUSTED_PROXIES` : IPs de confiance pour le proxy inverse

**Configuration initiale requise :**

1. **Génération des clés JWT (première installation) :**

   ```bash
   # Accéder au conteneur backend
   docker-compose exec backend bash

   # Générer les clés JWT
   php bin/console lexik:jwt:generate-keypair

   # Ou manuellement :
   mkdir -p config/jwt
   openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
   openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
   ```

2. **Migration de la base de données :**

   ```bash
   # Exécuter les migrations
   docker-compose exec backend php bin/console doctrine:migrations:migrate

   # Charger les données de test (optionnel)
   docker-compose exec backend php bin/console doctrine:fixtures:load
   ```

### Rechargement à chaud

- **Frontend** : Serveur de développement Angular avec rechargement à chaud
- **Backend** : FrankenPHP avec surveillance de fichiers activée

### Débogage

- Xdebug est disponible pour le backend (désactivé par défaut)
- Activer en définissant `XDEBUG_MODE=debug` dans l'environnement

## Production

Pour un déploiement en production :

1. Mettre à jour les variables d'environnement pour la production :

   ```bash
   APP_ENV=prod
   APP_SECRET=votre-secret-production
   POSTGRES_PASSWORD=votre-mot-de-passe-securise
   ```

2. Utiliser la cible de production pour Symfony :

   ```yaml
   backend:
     build:
       target: frankenphp_prod
   ```

3. Considérer l'utilisation d'une base de données externe et Redis pour le stockage de session

### Problèmes courants

1. **Conflits de ports** : Changer les ports dans le fichier `.env` si nécessaire
2. **Connexion à la base de données** : S'assurer que la base de données est démarrée avant le backend
3. **Problèmes de construction** : Vider le cache de construction Docker : `docker-compose build --no-cache`
4. **Redirection de connexion vers le frontend** : Ce problème courant a été résolu en mettant à jour la configuration de sécurité Symfony
5. **Erreurs 404 sur les routes backend** : S'assurer d'accéder aux routes backend via le proxy (http://localhost/manager/) et non directement
6. **Erreurs JWT** : Vérifier que les clés JWT ont été générées et que `JWT_PASSPHRASE` correspond à la phrase de passe utilisée
7. **Variables d'environnement manquantes** :
   - Vérifier que les fichiers `.env` existent dans la racine ET dans `access_mns_manager/`
   - S'assurer que `APP_SECRET` et `JWT_PASSPHRASE` sont définis
   - Copier les fichiers `.env.example` si nécessaire
8. **Problèmes de permissions** : Sur Linux/macOS, s'assurer que les dossiers `var/` et `config/jwt/` ont les bonnes permissions

### Statut des conteneurs

```bash
# Vérifier le statut des conteneurs
docker-compose ps

# Vérifier la santé des conteneurs
docker-compose exec backend curl -f http://localhost:2019/metrics
```

### Tout réinitialiser

```bash
# Arrêter et supprimer tout
docker-compose down -v --remove-orphans

# Nettoyer le système Docker
docker system prune -a
```

## Architecture Réseau

```
                   Internet/Navigateur
                           |
                    Requêtes HTTP
                           |
                           ↓
                  ┌─────────────────┐
                  │   Proxy Nginx   │
                  │     (:80)       │
                  └─────────────────┘
                           |
              ┌────────────┼────────────┐
              │            │            │
              ↓            ↓            ↓
    ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
    │   Routes    │  │   Routes    │  │   Routes    │
    │ /manager/*  │  │   /api/*    │  │     /*      │
    │ /assets/*   │  │             │  │ (catch-all) │
    │/.well-known/│  │             │  │             │
    │  mercure    │  │             │  │             │
    └─────────────┘  └─────────────┘  └─────────────┘
              │            │            │
              ↓            ↓            ↓
    ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
    │   Backend   │  │   Backend   │  │  Frontend   │
    │ (Symfony)   │  │    (API)    │  │ (Angular)   │
    │   :8000     │  │   :8000     │  │   :4200     │
    └─────────────┘  └─────────────┘  └─────────────┘
              │            │
              └────────────┘
                     │
                     ↓
              ┌─────────────┐
              │Base de données│
              │ PostgreSQL  │
              │   :5432     │
              └─────────────┘

    Réseau Docker : app-network
```

### Détails du routage

- **Routes frontend** : `http://localhost/` → Application Angular (catch-all)
- **Manager backend** : `http://localhost/manager/*` → Interface d'administration Symfony
- **API backend** : `http://localhost/api/*` → Points d'accès API REST
- **Ressources backend** : `http://localhost/assets/*` → Fichiers statiques (CSS, JS, images) de Symfony
- **Hub Mercure** : `http://localhost/.well-known/mercure` → Messagerie temps réel (support WebSocket)

**Priorité des routes** : nginx fait correspondre les routes par ordre de spécificité :

1. `/manager/*` (plus spécifique)
2. `/api/*`
3. `/assets/*`
4. `/.well-known/mercure`
5. `/*` (catch-all pour le frontend)

Tous les services communiquent via un réseau Docker dédié (`app-network`).
