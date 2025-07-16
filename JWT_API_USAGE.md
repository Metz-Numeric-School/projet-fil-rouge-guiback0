# JWT API Usage Guide

## Overview

This API uses JWT (JSON Web Tokens) for authentication. Your application has two authentication endpoints:

- `/api/login_check` - Standard JWT authentication endpoint
- `/api/auth/login` - Custom authentication endpoint with additional user information

## Configuration

Your JWT configuration (from `config/packages/lexik_jwt_authentication.yaml`):

- **Token TTL**: 3600 seconds (1 hour)
- **Algorithm**: RS256 (RSA with SHA-256)
- **Authorization Header**: `Authorization: Bearer {token}`

## 1. Obtaining a Token

### Method 1: Standard JWT Endpoint

**Endpoint**: `POST /api/login_check`

**Request**:

```bash
# Linux/macOS
curl -X POST -H "Content-Type: application/json" https://localhost/api/login_check -d '{"username":"johndoe@example.com","password":"test"}'

# Windows
curl -X POST -H "Content-Type: application/json" https://localhost/api/login_check --data {\"username\":\"johndoe@example.com\",\"password\":\"test\"}
```

**Response**:

```json
{
  "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

### Method 2: Custom Authentication Endpoint (Recommended)

**Endpoint**: `POST /api/auth/login`

**Request**:

```bash
# Linux/macOS
curl -X POST -H "Content-Type: application/json" https://localhost/api/auth/login -d '{"email":"johndoe@example.com","password":"test"}'

# Windows
curl -X POST -H "Content-Type: application/json" https://localhost/api/auth/login --data {\"email\":\"johndoe@example.com\",\"password\":\"test\"}
```

**Response**:

```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "email": "johndoe@example.com",
      "nom": "Doe",
      "prenom": "John",
      "roles": ["ROLE_USER"]
    },
    "organisation": {
      "id": 1,
      "nom": "Example Organization"
    }
  },
  "message": "Connexion réussie"
}
```

## 2. Using the Token

### Authorization Header (Default)

Include the token in the `Authorization` header with the `Bearer` prefix:

```bash
curl -H "Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..." https://localhost/api/users
```

### JavaScript Example

```javascript
// Login function
async function login(email, password) {
  const response = await fetch("/api/auth/login", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      email: email,
      password: password,
    }),
  });

  const data = await response.json();

  if (data.success) {
    // Store token in localStorage or sessionStorage
    localStorage.setItem("jwt_token", data.data.token);
    return data.data;
  } else {
    throw new Error(data.message);
  }
}

// API call with token
async function makeAuthenticatedRequest(endpoint) {
  const token = localStorage.getItem("jwt_token");

  const response = await fetch(endpoint, {
    headers: {
      Authorization: `Bearer ${token}`,
      "Content-Type": "application/json",
    },
  });

  return response.json();
}
```

## 3. Available API Endpoints

Based on your current setup, here are the available API endpoints:

### Authentication

- `POST /api/login_check` - Standard JWT login
- `POST /api/auth/login` - Custom login with user info
- `POST /api/auth/refresh` - Refresh JWT token

### API Resources

- `/api/users` - User management
- `/api/badges` - Badge management
- `/api/zones` - Zone management
- `/api/presence` - Presence tracking

## 4. Error Handling

### Authentication Errors

**Invalid Credentials**:

```json
{
  "success": false,
  "error": "INVALID_CREDENTIALS",
  "message": "Identifiants invalides"
}
```

**Missing Credentials**:

```json
{
  "success": false,
  "error": "MISSING_CREDENTIALS",
  "message": "Email et mot de passe requis"
}
```

### Token Expiration

When a token expires, you'll receive a `401 Unauthorized` response. You need to:

1. Redirect user to login
2. Use the refresh token endpoint if available
3. Re-authenticate the user

## 5. Security Notes

### Token Storage

- **Client-side**: Store tokens securely (HttpOnly cookies preferred over localStorage)
- **Server-side**: Tokens are stateless and verified using public key cryptography

### Token Lifespan

- Default TTL: 3600 seconds (1 hour)
- Tokens automatically expire and cannot be renewed without re-authentication

### CORS Configuration

If working with cross-origin requests, ensure your CORS settings allow the `Authorization` header.

### Apache Configuration

If using Apache, add this to your VirtualHost configuration:

```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

## 6. Testing Examples

### Test Authentication

```bash
# Test login
curl -X POST -H "Content-Type: application/json" \
  https://localhost/api/auth/login \
  -d '{"email":"test@example.com","password":"password123"}'

# Test protected endpoint
curl -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  https://localhost/api/users
```

### Test Token Refresh

```bash
curl -X POST -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  https://localhost/api/auth/refresh
```

## 7. Development Tips

1. **Token Debugging**: Decode JWT tokens at [jwt.io](https://jwt.io) to inspect claims
2. **Logging**: Check Symfony logs for authentication issues
3. **Environment**: Ensure JWT keys are properly configured in your `.env` file
4. **Testing**: Use tools like Postman or Insomnia for API testing

## Environment Variables

Make sure these are set in your `.env` file:

```bash
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase
```
