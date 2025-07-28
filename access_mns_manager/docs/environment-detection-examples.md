# Environment Detection in Symfony Controllers

This document shows different ways to detect whether you're running in `dev`, `prod`, or `test` environment in your Symfony application.

## Method 1: Using `getParameter()` in AbstractController (Recommended)

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function someAction(): Response
    {
        $environment = $this->getParameter('kernel.environment');

        $isProd = $environment === 'prod';
        $isDev = $environment === 'dev';
        $isTest = $environment === 'test';

        // Use the environment information
        if ($isProd) {
            // Production logic
        } elseif ($isDev) {
            // Development logic
        }

        return $this->render('template.html.twig', [
            'environment' => $environment
        ]);
    }
}
```

## Method 2: Using Dependency Injection

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MyController extends AbstractController
{
    private string $environment;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->environment = $parameterBag->get('kernel.environment');
    }

    public function someAction(): Response
    {
        $isProd = $this->environment === 'prod';

        // Use the environment information
        return $this->render('template.html.twig');
    }
}
```

## Method 3: Injecting Environment Directly

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    private string $environment;

    public function __construct(string $kernelEnvironment)
    {
        $this->environment = $kernelEnvironment;
    }

    public function someAction(): Response
    {
        $isProd = $this->environment === 'prod';

        return $this->render('template.html.twig');
    }
}
```

For this method, you need to configure the service in `config/services.yaml`:

```yaml
services:
    App\Controller\MyController:
        arguments:
            $kernelEnvironment: "%kernel.environment%"
```

## Method 4: Using $\_ENV or $\_SERVER (Not recommended)

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function someAction(): Response
    {
        // Using $_ENV (if properly loaded)
        $environment = $_ENV['APP_ENV'] ?? 'dev';

        // Or using $_SERVER
        $environment = $_SERVER['APP_ENV'] ?? 'dev';

        $isProd = $environment === 'prod';

        return $this->render('template.html.twig');
    }
}
```

## Method 5: Using Kernel Debug Parameter

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function someAction(): Response
    {
        $isDebug = $this->getParameter('kernel.debug');

        // kernel.debug is typically:
        // - true in dev/test environments
        // - false in prod environment

        if ($isDebug) {
            // Debug mode (usually dev/test)
        } else {
            // Production mode
        }

        return $this->render('template.html.twig');
    }
}
```

## Practical Examples

### Security Controller with Environment-Specific Logic

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $environment = $this->getParameter('kernel.environment');
        $isDebug = $this->getParameter('kernel.debug');

        // Environment-specific behavior
        $loginAttemptLimit = match($environment) {
            'prod' => 3,
            'dev' => 10,
            'test' => 1,
            default => 5
        };

        // Show debug info only in development
        $debugInfo = $isDebug ? [
            'environment' => $environment,
            'login_limit' => $loginAttemptLimit,
            'server_time' => new \DateTime(),
        ] : [];

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'debug_info' => $debugInfo,
            'environment' => $environment,
        ]);
    }
}
```

## Using in Twig Templates

You can also pass the environment to your Twig templates and use it there:

```twig
{# security/login.html.twig #}
{% if environment == 'dev' %}
    <div class="alert alert-info">
        <strong>Development Mode</strong> - Additional debug information available
    </div>
{% endif %}

{% if environment == 'prod' %}
    <div class="alert alert-warning">
        <strong>Production Mode</strong> - Enhanced security enabled
    </div>
{% endif %}
```

## Recommendations

1. **Use Method 1** (`getParameter('kernel.environment')`) for most cases - it's simple and works well with AbstractController
2. **Use Method 2 or 3** if you need the environment in multiple methods of the same controller
3. **Avoid Method 4** ($_ENV/$\_SERVER) as it bypasses Symfony's parameter system
4. **Use Method 5** (`kernel.debug`) when you specifically need to know if debug mode is enabled

## Common Environment Values

-   `dev` - Development environment
-   `prod` - Production environment
-   `test` - Testing environment (used during automated tests)

The environment is set via the `APP_ENV` environment variable or the `.env` file in your project root.
