# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

ACCESS MNS is a full-stack access management system with:
- **Backend**: Symfony 7.2 API (access_mns_manager) with JWT authentication, PostgreSQL database
- **Frontend**: Angular 19 client (access_mns_client) with Material UI components
- **Infrastructure**: Docker Compose with PostgreSQL, Nginx reverse proxy

## Development Commands

### Backend (Symfony - access_mns_manager/)
```bash
# Start development environment
docker-compose up -d

# Symfony console commands
docker exec -it access_mns_manager php bin/console [command]

# Database operations
docker exec -it access_mns_manager php bin/console doctrine:migrations:migrate
docker exec -it access_mns_manager php bin/console doctrine:fixtures:load

# Cache and assets
docker exec -it access_mns_manager php bin/console cache:clear
docker exec -it access_mns_manager php bin/console assets:install

# Testing
docker exec -it access_mns_manager php bin/phpunit

# Code generation (using MakerBundle)
docker exec -it access_mns_manager php bin/console make:entity
docker exec -it access_mns_manager php bin/console make:controller
docker exec -it access_mns_manager php bin/console make:migration
```

### Frontend (Angular - access_mns_client/)
```bash
# Development server
cd access_mns_client && npm start  # or ng serve

# Build for production
cd access_mns_client && npm run build

# Run tests
cd access_mns_client && npm test

# Watch build
cd access_mns_client && npm run watch

# Angular CLI commands
cd access_mns_client && ng generate component [name]
cd access_mns_client && ng generate service [name]
cd access_mns_client && ng generate guard [name]
```

### Docker Operations
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f [service_name]

# Rebuild services
docker-compose build --no-cache

# Stop services
docker-compose down
```

## Architecture & Key Components

### Backend (Symfony)
- **Architecture**: Monolithic Symfony 7.2 app with FrankenPHP runtime
- **Authentication**: JWT-based auth via LexikJWTAuthenticationBundle
- **API Controllers**: Located in `src/Controller/API/` with JSON responses
- **Web Controllers**: Traditional Symfony controllers with Twig templates for admin interface
- **Entities**: Doctrine ORM entities for User, Organisation, Badge, Service, Zone, Pointage
- **Security**: Role-based access (ROLE_USER, ROLE_ADMIN, ROLE_SUPER_ADMIN)
- **Database**: PostgreSQL with Doctrine migrations
- **Services**: Custom business logic in `src/Service/` (BadgeService, OrganisationService, PresenceService)

### Frontend (Angular)
- **Architecture**: Standalone components with Angular 19
- **Authentication**: JWT token management with automatic refresh
- **Services**: HTTP client services for API communication
- **Routing**: Angular Router with authentication guards
- **UI**: Angular Material components with custom SCSS

### Key Authentication Flow
1. Login via `/api/auth/login` returns JWT token + user data
2. Token stored in localStorage/sessionStorage based on "Remember Me"
3. All API requests include `Authorization: Bearer [token]` header
4. Token refresh available at `/api/auth/refresh`
5. User profile data at `/api/auth/me`

### Database Entities
Core entities include User, Organisation, Service, Badge, Zone, Pointage (time tracking), with relationships managed through junction tables like Travailler, UserBadge, ServiceZone.

### API Endpoints Structure
- Authentication: `/api/auth/*`
- User management: `/api/user/*`
- Core business logic accessible through standard CRUD controllers

## Environment Configuration

- Backend runs on port 8000 (configurable via MANAGER_PORT)
- Frontend runs on port 4200 (configurable via CLIENT_PORT)
- Database on port 5432
- Nginx proxy on ports 80/443

## Docker Services
- `database`: PostgreSQL container
- `backend`: Symfony with FrankenPHP
- `frontend`: Angular development server
- `proxy`: Nginx reverse proxy (optional)