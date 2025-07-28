# API User Endpoints Documentation

## Overview

This document describes the user-related API endpoints that provide comprehensive user information for frontend applications.

## Authentication

All endpoints require JWT authentication. Include the token in the Authorization header:

```
Authorization: Bearer <your-jwt-token>
```

## Endpoints

### 1. Get Complete User Profile

**URL:** `GET /manager/api/user/profile/complete`  
**Authentication:** Required (User must be authenticated)  
**Description:** Retrieves all comprehensive information about the authenticated user.

#### Response Structure

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "email": "user@example.com",
            "nom": "Doe",
            "prenom": "John",
            "telephone": "+33123456789",
            "date_naissance": "1990-01-15",
            "date_inscription": "2024-01-01",
            "adresse": "123 Main Street",
            "poste": "Developer",
            "horraire": "08:00",
            "heure_debut": "09:00",
            "jours_semaine_travaille": 5,
            "roles": ["ROLE_USER"]
        },
        "organisation": {
            "id": 1,
            "nom_organisation": "ACME Corp",
            "email": "contact@acme.com",
            "telephone": "+33987654321",
            "site_web": "https://acme.com",
            "siret": "12345678901234",
            "adresse": {
                "numero_rue": 123,
                "suffix_rue": "bis",
                "nom_rue": "Avenue des Champs",
                "code_postal": "75008",
                "ville": "Paris",
                "pays": "France"
            }
        },
        "services": {
            "current": {
                "id": 2,
                "nom_service": "IT Department",
                "niveau_service": 3,
                "date_debut": "2024-01-01",
                "date_fin": null,
                "is_current": true
            },
            "history": [
                {
                    "id": 1,
                    "nom_service": "HR Department",
                    "niveau_service": 2,
                    "date_debut": "2023-06-01",
                    "date_fin": "2023-12-31",
                    "is_current": false
                }
            ]
        },
        "zones_accessibles": [
            {
                "id": 1,
                "nom_zone": "Server Room",
                "description": "Restricted access server room",
                "capacite": 10
            },
            {
                "id": 2,
                "nom_zone": "Office Floor 2",
                "description": "Second floor office space",
                "capacite": 50
            }
        ],
        "badges": [
            {
                "id": 1,
                "numero_badge": 12345,
                "type_badge": "RFID",
                "date_creation": "2024-01-01",
                "date_expiration": "2025-01-01",
                "is_active": true
            }
        ],
        "acces_autorises": [
            {
                "id": 1,
                "numero_badgeuse": 101,
                "date_installation": "2024-01-01 10:00:00",
                "zone": {
                    "id": 1,
                    "nom_zone": "Server Room"
                },
                "badgeuse": {
                    "id": 1,
                    "reference": "BADGE-001",
                    "date_installation": "2024-01-01"
                }
            }
        ],
        "badgeuses_autorisees": [
            {
                "id": 1,
                "reference": "BADGE-001",
                "date_installation": "2024-01-01",
                "zones_accessibles": [
                    {
                        "id": 1,
                        "nom_zone": "Server Room"
                    },
                    {
                        "id": 2,
                        "nom_zone": "Office Floor 2"
                    }
                ]
            }
        ]
    }
}
```

#### Data Explanation

-   **user**: Complete user information (excluding password for security)
-   **organisation**: Organization where the user works, including complete address
-   **services**:
    -   `current`: User's current service assignment
    -   `history`: Complete history of service assignments
-   **zones_accessibles**: All zones the user can access based on their current service
-   **badges**: All badges assigned to the user with expiration status
-   **acces_autorises**: Detailed access permissions showing which badgeuse can scan in which zones
-   **badgeuses_autorisees**: List of badge scanners the user can use, grouped by zones

### 2. Get User Profile by ID (Admin Only)

**URL:** `GET /manager/api/user/profile/{id}`  
**Authentication:** Required (Admin role: ROLE_ADMIN)  
**Description:** Retrieves basic user information for a specific user by ID.

#### Parameters

-   `id` (integer): The user ID to retrieve

#### Response Structure

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "email": "user@example.com",
            "nom": "Doe",
            "prenom": "John",
            "telephone": "+33123456789",
            "date_naissance": "1990-01-15",
            "date_inscription": "2024-01-01",
            "adresse": "123 Main Street",
            "poste": "Developer",
            "roles": ["ROLE_USER"]
        },
        "message": "Informations utilisateur récupérées avec succès"
    }
}
```

## Error Responses

### Authentication Errors

```json
{
    "success": false,
    "error": "INVALID_USER",
    "message": "Utilisateur invalide"
}
```

### Not Found Errors (for profile by ID)

```json
{
    "success": false,
    "error": "USER_NOT_FOUND",
    "message": "Utilisateur non trouvé"
}
```

### Server Errors

```json
{
    "success": false,
    "error": "SERVER_ERROR",
    "message": "Erreur lors de la récupération des informations utilisateur",
    "details": "Specific error message"
}
```

## Usage Examples

### JavaScript/Fetch

```javascript
// Get complete profile for authenticated user
const response = await fetch("/manager/api/user/profile/complete", {
    method: "GET",
    headers: {
        Authorization: "Bearer " + localStorage.getItem("jwt_token"),
        "Content-Type": "application/json",
    },
});

const data = await response.json();
if (data.success) {
    console.log("User data:", data.data);
} else {
    console.error("Error:", data.message);
}
```

### curl

```bash
# Get complete profile
curl -X GET \
  '/manager/api/user/profile/complete' \
  -H 'Authorization: Bearer YOUR_JWT_TOKEN' \
  -H 'Content-Type: application/json'

# Get user by ID (admin only)
curl -X GET \
  '/manager/api/user/profile/123' \
  -H 'Authorization: Bearer YOUR_JWT_TOKEN' \
  -H 'Content-Type: application/json'
```

## Integration Notes

1. **Frontend Integration**: Use the `/profile/complete` endpoint to populate user dashboards with comprehensive information
2. **Access Control**: The API automatically determines user access based on their current service assignment
3. **Badge Management**: The `badges` array includes active status to help frontend show valid/expired badges
4. **Zone Access**: The `zones_accessibles` and `badgeuses_autorisees` provide everything needed for access control UI
5. **Security**: Passwords are never included in API responses for security reasons
