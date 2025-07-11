# Reverse Proxy Configuration for Access MNS

This document explains how the reverse proxy configuration works in the Access MNS project.

## Overview

The Access MNS project uses an Nginx reverse proxy to route traffic between the Angular frontend and Symfony backend. The Symfony application is configured to trust forwarded headers from the reverse proxy.

## Configuration Files

### 1. Symfony Framework Configuration

**File**: `access_mns_manager/config/packages/framework.yaml`

```yaml
framework:
  # Trust Docker internal network addresses and localhost
  trusted_proxies: "%env(TRUSTED_PROXIES)%"
  trusted_headers:
    [
      "x-forwarded-for",
      "x-forwarded-host",
      "x-forwarded-proto",
      "x-forwarded-port",
      "x-forwarded-prefix",
    ]
```

### 2. Environment Variables

**File**: `access_mns_manager/.env`

```env
# Trust Docker network and localhost IPs
TRUSTED_PROXIES=127.0.0.1,172.16.0.0/12,10.0.0.0/8,192.168.0.0/16
```

**Root .env file**:

```env
TRUSTED_PROXIES=127.0.0.1,172.16.0.0/12,10.0.0.0/8,192.168.0.0/16
```

### 3. Nginx Configuration

**File**: `nginx/nginx.conf`

The Nginx configuration includes all necessary headers for reverse proxy:

- `X-Forwarded-For`: Client's real IP address
- `X-Forwarded-Proto`: Original protocol (http/https)
- `X-Forwarded-Host`: Original host header
- `X-Forwarded-Port`: Original port
- `X-Forwarded-Prefix`: Subpath prefix for URLs

## How It Works

1. **Client Request**: A client makes a request to the Nginx proxy
2. **Header Addition**: Nginx adds the `X-Forwarded-*` headers to the request
3. **Backend Routing**: The request is forwarded to the appropriate backend service
4. **Header Processing**: Symfony reads the trusted headers and adjusts request information
5. **Response Generation**: Symfony generates the correct URLs and redirects

## Security Considerations

### Development Environment

- Trusts all Docker internal networks (`172.16.0.0/12`, `10.0.0.0/8`, `192.168.0.0/16`)
- Trusts localhost (`127.0.0.1`)

### Production Environment

- **Important**: Update `TRUSTED_PROXIES` to include only your actual proxy IPs
- Never use `REMOTE_ADDR` in production unless you're certain only trusted proxies can reach your application
- Consider using specific IP ranges instead of broad network ranges

## Common Use Cases

### Behind AWS Load Balancer

```env
# For AWS ALB with dynamic IPs, you might need:
TRUSTED_PROXIES=127.0.0.1,REMOTE_ADDR
```

### Behind CloudFront

```env
# Add CloudFront IP ranges to trusted proxies
TRUSTED_PROXIES=127.0.0.1,REMOTE_ADDR,54.240.0.0/12,52.46.0.0/18
```

### With Custom Headers

If your proxy uses custom headers, modify the Symfony configuration:

```yaml
framework:
  trusted_headers: ["custom-forwarded-for", "custom-forwarded-proto"]
```

## Troubleshooting

### Incorrect Client IP

- Check that `X-Forwarded-For` header is being set by the proxy
- Verify that the proxy IP is in the `TRUSTED_PROXIES` list

### Wrong Protocol Detection

- Ensure `X-Forwarded-Proto` is set correctly (http/https)
- Check that the header is trusted in the Symfony configuration

### URL Generation Issues

- Verify `X-Forwarded-Host` and `X-Forwarded-Port` are being set
- For subpath deployments, ensure `X-Forwarded-Prefix` is configured

### Testing

To test the configuration:

1. Start the services:

   ```bash
   docker-compose up -d
   ```

2. Check the headers being received by the backend:
   ```bash
   # Add this to a Symfony controller for debugging
   public function debugHeaders(Request $request): Response
   {
       return new JsonResponse([
           'client_ip' => $request->getClientIp(),
           'scheme' => $request->getScheme(),
           'host' => $request->getHost(),
           'port' => $request->getPort(),
           'headers' => $request->headers->all(),
       ]);
   }
   ```

## HTTPS Configuration

For HTTPS, rename `nginx/conf.d/https.conf.template` to `nginx/conf.d/https.conf` and:

1. Add your SSL certificates to the `ssl/` directory
2. Update the certificate paths in the configuration
3. Restart the proxy service

## Additional Security

### Rate Limiting

Consider adding rate limiting to the Nginx configuration:

```nginx
http {
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;

    server {
        location /api/ {
            limit_req zone=api burst=20 nodelay;
            # ... rest of configuration
        }
    }
}
```

### IP Whitelisting

For admin routes, consider IP whitelisting:

```nginx
location /admin/ {
    allow 192.168.1.0/24;
    deny all;
    # ... rest of configuration
}
```
