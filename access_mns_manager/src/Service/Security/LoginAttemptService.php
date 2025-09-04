<?php

namespace App\Service\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class LoginAttemptService
{
    public function __construct(
        private RateLimiterFactory $loginAttemptLimiter
    ) {}

    /**
     * Check if login attempt is allowed for given IP address
     */
    public function checkAttempt(Request $request): void
    {
        $limiter = $this->loginAttemptLimiter->create($this->getClientId($request));
        
        $reservation = $limiter->consume(1);
        if (!$reservation->isAccepted()) {
            throw new TooManyLoginAttemptsAuthenticationException(
                3, // threshold
                'Trop de tentatives de connexion. Veuillez réessayer dans 15 minutes.'
            );
        }
    }

    /**
     * Check if user can attempt login without consuming attempts
     */
    public function canAttempt(Request $request): bool
    {
        $limiter = $this->loginAttemptLimiter->create($this->getClientId($request));
        
        return $limiter->consume(0)->isAccepted();
    }

    /**
     * Reset attempts for given request (called on successful login)
     */
    public function resetAttempts(Request $request): void
    {
        $limiter = $this->loginAttemptLimiter->create($this->getClientId($request));
        $limiter->reset();
    }

    /**
     * Get remaining attempts
     */
    public function getRemainingAttempts(Request $request): int
    {
        $limiter = $this->loginAttemptLimiter->create($this->getClientId($request));
        $reservation = $limiter->consume(0);
        
        return $reservation->getRemainingTokens();
    }

    /**
     * Get client identifier (IP address)
     */
    private function getClientId(Request $request): string
    {
        return 'login_attempt_' . $request->getClientIp();
    }
}