# JWT API - Corrected Usage Guide

## Current Status

Your Symfony application is running on `http://127.0.0.1:8000` with the following JWT API endpoints:

## API Endpoints

### Authentication Endpoints

- **Custom Authentication**: `POST /manager/api/auth/login`
- **Standard JWT**: `POST /api/login_check`
- **Token Refresh**: `POST /manager/api/auth/refresh`

### Protected Endpoints (require token)

- **Users**: `GET /manager/api/users`
- **Badges**: `GET /manager/api/badge`
- **Zones**: `GET /manager/api/zones`
- **Presence**: `GET /manager/api/presence/*`

## Working PowerShell Commands

### 1. Test Custom Authentication

```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/manager/api/auth/login" -Method POST -Body '{"email":"admin@example.com","password":"admin123"}' -ContentType "application/json"
```

### 2. Test Standard JWT

```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/login_check" -Method POST -Body '{"username":"admin@example.com","password":"admin123"}' -ContentType "application/json"
```

### 3. Test Protected Endpoint

```powershell
$token = "YOUR_JWT_TOKEN_HERE"
Invoke-RestMethod -Uri "http://127.0.0.1:8000/manager/api/users" -Headers @{Authorization = "Bearer $token"}
```

## Test Credentials

Based on your `AppFixtures.php`:

### Admin User

- **Email**: `admin@example.com`
- **Password**: `admin123`
- **Roles**: `ROLE_ADMIN`

### Regular Users

- **Email**: `jean.dupont@example.com`
- **Password**: `password123`
- **Roles**: `ROLE_USER`

## Prerequisites

Before testing the JWT API, ensure:

1. **Symfony server is running**:

   ```bash
   cd access_mns_manager
   symfony server:start
   ```

2. **Database is set up**:

   ```bash
   cd access_mns_manager
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ```

3. **PostgreSQL is running**: Make sure your PostgreSQL server is started

## Current Issue

You're getting database connection errors:

```
SQLSTATE[08006] [7] connection to server at "127.0.0.1", port 5432 failed: FATAL: authentification par mot de passe échouée pour l'utilisateur « postgres »
```

This means PostgreSQL authentication is failing. Check your `.env` file in the `access_mns_manager` directory and ensure the database credentials are correct.

## Next Steps

1. **Fix database connection**: Update your `.env` file with correct PostgreSQL credentials
2. **Load fixtures**: Run `php bin/console doctrine:fixtures:load` to create test users
3. **Test authentication**: Use the PowerShell commands above to test JWT authentication

## Expected Response Format

### Successful Custom Authentication

```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "email": "admin@example.com",
      "nom": "Administrateur",
      "prenom": "Système",
      "roles": ["ROLE_ADMIN"]
    },
    "organisation": {
      "id": 1,
      "nom": "Ministère de la Défense"
    }
  },
  "message": "Connexion réussie"
}
```

### Successful Standard JWT Authentication

```json
{
  "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

Once the database connection is fixed, these commands should work properly for testing your JWT API.
