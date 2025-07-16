# JWT API Quick Start Guide

## Test Credentials

Based on your DataFixtures, you can use these test accounts:

### Admin User

- **Email**: `admin@example.com`
- **Password**: `admin123`
- **Roles**: `ROLE_ADMIN`

### Regular Users

- **Email**: `jean.dupont@example.com`
- **Password**: `password123`
- **Roles**: `ROLE_USER`

- **Email**: `marie.martin@example.com`
- **Password**: `password123`
- **Roles**: `ROLE_USER`

## Quick Test Commands

### 1. Test Authentication (Windows PowerShell)

```powershell
# Test admin login
curl -X POST -H "Content-Type: application/json" https://localhost/api/auth/login --data '{\"email\":\"admin@example.com\",\"password\":\"admin123\"}'

# Test regular user login
curl -X POST -H "Content-Type: application/json" https://localhost/api/auth/login --data '{\"email\":\"jean.dupont@example.com\",\"password\":\"password123\"}'
```

### 2. Test Authentication (Linux/macOS)

```bash
# Test admin login
curl -X POST -H "Content-Type: application/json" https://localhost/api/auth/login -d '{"email":"admin@example.com","password":"admin123"}'

# Test regular user login
curl -X POST -H "Content-Type: application/json" https://localhost/api/auth/login -d '{"email":"jean.dupont@example.com","password":"password123"}'
```

### 3. Example Success Response

```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3MzcwMjk1MjYsImV4cCI6MTczNzAzMzEyNiwidXNlcm5hbWUiOiJhZG1pbkBleGFtcGxlLmNvbSIsInJvbGVzIjpbIlJPTEVfQURNSU4iXX0...",
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

### 4. Use Token for Protected Endpoints

```powershell
# Replace YOUR_TOKEN_HERE with the actual token from step 1
curl -H "Authorization: Bearer YOUR_TOKEN_HERE" https://localhost/api/users
```

## PowerShell Test Script

Save this as `test-jwt.ps1`:

```powershell
# Test JWT Authentication
$BaseUrl = "https://localhost"

# Test admin login
Write-Host "Testing admin login..." -ForegroundColor Yellow
$loginData = @{
    email = "admin@example.com"
    password = "admin123"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$BaseUrl/api/auth/login" -Method POST -Body $loginData -ContentType "application/json" -SkipCertificateCheck

    if ($response.success) {
        Write-Host "✅ Admin login successful!" -ForegroundColor Green
        Write-Host "User: $($response.data.user.prenom) $($response.data.user.nom)" -ForegroundColor Cyan
        Write-Host "Organization: $($response.data.organisation.nom)" -ForegroundColor Cyan

        $token = $response.data.token
        Write-Host "Token (first 50 chars): $($token.Substring(0, 50))..." -ForegroundColor Gray

        # Test protected endpoint
        Write-Host "`nTesting protected endpoint..." -ForegroundColor Yellow
        $headers = @{
            Authorization = "Bearer $token"
        }

        $apiResponse = Invoke-RestMethod -Uri "$BaseUrl/api/users" -Headers $headers -SkipCertificateCheck
        Write-Host "✅ Protected endpoint accessible!" -ForegroundColor Green

    } else {
        Write-Host "❌ Login failed: $($response.message)" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test regular user login
Write-Host "`nTesting regular user login..." -ForegroundColor Yellow
$userLoginData = @{
    email = "jean.dupont@example.com"
    password = "password123"
} | ConvertTo-Json

try {
    $userResponse = Invoke-RestMethod -Uri "$BaseUrl/api/auth/login" -Method POST -Body $userLoginData -ContentType "application/json" -SkipCertificateCheck

    if ($userResponse.success) {
        Write-Host "✅ User login successful!" -ForegroundColor Green
        Write-Host "User: $($userResponse.data.user.prenom) $($userResponse.data.user.nom)" -ForegroundColor Cyan
    } else {
        Write-Host "❌ User login failed: $($userResponse.message)" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
}
```

Run with: `powershell -ExecutionPolicy Bypass -File test-jwt.ps1`

## JavaScript Test Example

```javascript
// Test JWT authentication
async function testJWTAuth() {
  const baseUrl = "https://localhost";

  try {
    // Test admin login
    console.log("🔐 Testing admin login...");
    const response = await fetch(`${baseUrl}/api/auth/login`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        email: "admin@example.com",
        password: "admin123",
      }),
    });

    const data = await response.json();

    if (data.success) {
      console.log("✅ Admin login successful!");
      console.log("User:", data.data.user);
      console.log("Organization:", data.data.organisation);

      // Test protected endpoint
      console.log("\n🔒 Testing protected endpoint...");
      const protectedResponse = await fetch(`${baseUrl}/api/users`, {
        headers: {
          Authorization: `Bearer ${data.data.token}`,
        },
      });

      if (protectedResponse.ok) {
        console.log("✅ Protected endpoint accessible!");
      } else {
        console.log("❌ Protected endpoint failed");
      }
    } else {
      console.log("❌ Login failed:", data.message);
    }
  } catch (error) {
    console.error("❌ Error:", error.message);
  }
}

testJWTAuth();
```

## cURL Examples with Real Data

### Admin Login

```bash
curl -X POST -H "Content-Type: application/json" \
  https://localhost/api/auth/login \
  -d '{"email":"admin@example.com","password":"admin123"}' \
  -k
```

### Regular User Login

```bash
curl -X POST -H "Content-Type: application/json" \
  https://localhost/api/auth/login \
  -d '{"email":"jean.dupont@example.com","password":"password123"}' \
  -k
```

### Standard JWT Endpoint

```bash
curl -X POST -H "Content-Type: application/json" \
  https://localhost/api/login_check \
  -d '{"username":"admin@example.com","password":"admin123"}' \
  -k
```

## Available API Endpoints

Based on your controllers:

- **Authentication**:

  - `POST /api/auth/login` - Login with email/password
  - `POST /api/auth/refresh` - Refresh token
  - `POST /api/login_check` - Standard JWT login

- **API Resources**:
  - `GET /api/users` - Get users
  - `GET /api/badges` - Get badges
  - `GET /api/zones` - Get zones
  - `GET /api/presence` - Get presence data

## Next Steps

1. **Load Test Data**: Make sure your fixtures are loaded:

   ```bash
   php bin/console doctrine:fixtures:load
   ```

2. **Test Authentication**: Use the test scripts above

3. **Check Token Expiration**: Tokens expire after 1 hour (3600 seconds)

4. **CORS Configuration**: If testing from a web browser, ensure CORS is configured properly

5. **SSL Certificate**: For development, you might need to ignore SSL certificate errors using `-k` flag with curl

## Troubleshooting

- **404 Not Found**: Check that your routes are properly configured
- **401 Unauthorized**: Token expired or invalid credentials
- **500 Internal Server Error**: Check Symfony logs in `var/log/`
- **CORS Issues**: Configure CORS bundle for cross-origin requests
- **SSL Issues**: Use `-k` flag with curl for development, or configure proper SSL certificates
