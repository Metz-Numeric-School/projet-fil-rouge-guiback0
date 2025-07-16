# JWT API Testing Examples

## Quick Test Commands

### 1. Test Custom Authentication Endpoint

```bash
# Windows PowerShell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/manager/api/auth/login" -Method POST -Body '{"email":"admin@example.com","password":"admin123"}' -ContentType "application/json"

# Windows PowerShell (alternative with curl.exe)
curl.exe -X POST -H "Content-Type: application/json" http://127.0.0.1:8000/manager/api/auth/login --data '{"email":"admin@example.com","password":"admin123"}'

# Linux/macOS
curl -X POST -H "Content-Type: application/json" http://127.0.0.1:8000/manager/api/auth/login -d '{"email":"admin@example.com","password":"admin123"}'
```

### 2. Test Standard JWT Endpoint

```bash
# Windows PowerShell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/login_check" -Method POST -Body '{"username":"admin@example.com","password":"admin123"}' -ContentType "application/json"

# Windows PowerShell (alternative with curl.exe)
curl.exe -X POST -H "Content-Type: application/json" http://127.0.0.1:8000/api/login_check --data '{"username":"admin@example.com","password":"admin123"}'

# Linux/macOS
curl -X POST -H "Content-Type: application/json" http://127.0.0.1:8000/api/login_check -d '{"username":"admin@example.com","password":"admin123"}'
```

### 3. Test Protected Endpoint

```bash
# Windows PowerShell
$token = "YOUR_TOKEN_HERE"
Invoke-RestMethod -Uri "http://127.0.0.1:8000/manager/api/users" -Headers @{Authorization = "Bearer $token"}

# Windows PowerShell (alternative with curl.exe)
curl.exe -H "Authorization: Bearer YOUR_TOKEN_HERE" http://127.0.0.1:8000/manager/api/users

# Linux/macOS
curl -H "Authorization: Bearer YOUR_TOKEN_HERE" http://127.0.0.1:8000/manager/api/users
```

### 4. Test Token Refresh

```bash
# Windows PowerShell
$token = "YOUR_TOKEN_HERE"
Invoke-RestMethod -Uri "http://127.0.0.1:8000/manager/api/auth/refresh" -Method POST -Headers @{Authorization = "Bearer $token"}

# Windows PowerShell (alternative with curl.exe)
curl.exe -X POST -H "Authorization: Bearer YOUR_TOKEN_HERE" http://127.0.0.1:8000/manager/api/auth/refresh

# Linux/macOS
curl -X POST -H "Authorization: Bearer YOUR_TOKEN_HERE" http://127.0.0.1:8000/manager/api/auth/refresh
```

## PowerShell Script Example

```powershell
# JWT_Test.ps1
param(
    [string]$BaseUrl = "http://127.0.0.1:8000",
    [string]$Email = "admin@example.com",
    [string]$Password = "admin123"
)

# Test authentication
$loginData = @{
    email = $Email
    password = $Password
} | ConvertTo-Json

try {
    Write-Host "Testing authentication..." -ForegroundColor Yellow
    $response = Invoke-RestMethod -Uri "$BaseUrl/manager/api/auth/login" -Method POST -Body $loginData -ContentType "application/json"

    if ($response.success) {
        Write-Host "✓ Authentication successful!" -ForegroundColor Green
        $token = $response.data.token
        Write-Host "Token: $token" -ForegroundColor Cyan

        # Test protected endpoint
        Write-Host "`nTesting protected endpoint..." -ForegroundColor Yellow
        $headers = @{
            Authorization = "Bearer $token"
        }

        $protectedResponse = Invoke-RestMethod -Uri "$BaseUrl/manager/api/users" -Headers $headers
        Write-Host "✓ Protected endpoint accessible!" -ForegroundColor Green
    } else {
        Write-Host "✗ Authentication failed: $($response.message)" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}
```

## JavaScript Test Example

```javascript
// jwt-test.js
const API_BASE_URL = "https://localhost";

async function testJWTAuth() {
  try {
    console.log("🔐 Testing JWT Authentication...");

    // 1. Login
    const loginResponse = await fetch(`${API_BASE_URL}/api/auth/login`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        email: "admin@example.com",
        password: "admin123",
      }),
    });

    const loginData = await loginResponse.json();

    if (loginData.success) {
      console.log("✅ Login successful!");
      console.log("Token:", loginData.data.token);
      console.log("User:", loginData.data.user);

      // 2. Test protected endpoint
      console.log("\n🔒 Testing protected endpoint...");
      const protectedResponse = await fetch(`${API_BASE_URL}/api/users`, {
        headers: {
          Authorization: `Bearer ${loginData.data.token}`,
          "Content-Type": "application/json",
        },
      });

      if (protectedResponse.ok) {
        console.log("✅ Protected endpoint accessible!");
        const userData = await protectedResponse.json();
        console.log("Response:", userData);
      } else {
        console.log("❌ Protected endpoint failed:", protectedResponse.status);
      }

      // 3. Test refresh
      console.log("\n🔄 Testing token refresh...");
      const refreshResponse = await fetch(`${API_BASE_URL}/api/auth/refresh`, {
        method: "POST",
        headers: {
          Authorization: `Bearer ${loginData.data.token}`,
          "Content-Type": "application/json",
        },
      });

      if (refreshResponse.ok) {
        console.log("✅ Token refresh successful!");
        const refreshData = await refreshResponse.json();
        console.log("New token:", refreshData.data.token);
      } else {
        console.log("❌ Token refresh failed:", refreshResponse.status);
      }
    } else {
      console.log("❌ Login failed:", loginData.message);
    }
  } catch (error) {
    console.error("❌ Error:", error.message);
  }
}

// Run the test
testJWTAuth();
```

## Postman Collection

Create a Postman collection with these requests:

### 1. Auth Login

- **Method**: POST
- **URL**: `{{base_url}}/api/auth/login`
- **Headers**: `Content-Type: application/json`
- **Body**:

```json
{
  "email": "admin@example.com",
  "password": "admin123"
}
```

### 2. Standard JWT Login

- **Method**: POST
- **URL**: `{{base_url}}/api/login_check`
- **Headers**: `Content-Type: application/json`
- **Body**:

```json
{
  "username": "admin@example.com",
  "password": "admin123"
}
```

### 3. Get Users (Protected)

- **Method**: GET
- **URL**: `{{base_url}}/api/users`
- **Headers**: `Authorization: Bearer {{token}}`

### 4. Refresh Token

- **Method**: POST
- **URL**: `{{base_url}}/api/auth/refresh`
- **Headers**: `Authorization: Bearer {{token}}`

## Environment Variables for Postman

```json
{
  "base_url": "https://localhost",
  "token": "{{jwt_token}}"
}
```

## Expected Responses

### Successful Authentication

```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "email": "admin@example.com",
      "nom": "Admin",
      "prenom": "User",
      "roles": ["ROLE_ADMIN"]
    },
    "organisation": {
      "id": 1,
      "nom": "My Organization"
    }
  },
  "message": "Connexion réussie"
}
```

### Authentication Failure

```json
{
  "success": false,
  "error": "INVALID_CREDENTIALS",
  "message": "Identifiants invalides"
}
```

### Token Expiration (401 Response)

```json
{
  "code": 401,
  "message": "JWT Token not found"
}
```

## Troubleshooting

### Database Connection Issues

If you get database connection errors, make sure:

1. **PostgreSQL is running**: Start your PostgreSQL server
2. **Database credentials are correct**: Check your `.env` file in the `access_mns_manager` directory
3. **Database exists**: Create the database if needed:
   ```bash
   cd access_mns_manager
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ```

### URL Structure

Based on your routing configuration:

- **Custom auth endpoint**: `http://127.0.0.1:8000/manager/api/auth/login`
- **Standard JWT endpoint**: `http://127.0.0.1:8000/api/login_check`
- **Protected endpoints**: `http://127.0.0.1:8000/manager/api/users`, etc.

### Common Issues

1. **SSL Certificate Issues**: Use `-k` flag with curl to ignore SSL errors during development
2. **CORS Issues**: Ensure your frontend domain is allowed in CORS configuration
3. **Token Format**: Always use `Bearer ` prefix (note the space after Bearer)
4. **Token Expiry**: Tokens expire after 1 hour by default
5. **Case Sensitivity**: Header names are case-sensitive (`Authorization` not `authorization`)
6. **JSON Format**: Make sure your JSON is properly formatted when testing with curl
7. **Database Connection**: Ensure PostgreSQL is running and accessible
