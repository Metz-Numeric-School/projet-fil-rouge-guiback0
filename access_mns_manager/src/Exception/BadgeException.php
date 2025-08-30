<?php

namespace App\Exception;

class BadgeException extends \Exception
{
    public const BADGE_NOT_FOUND = 'BADGE_NOT_FOUND';
    public const BADGE_EXPIRED = 'BADGE_EXPIRED';
    public const NO_ACTIVE_BADGE = 'NO_ACTIVE_BADGE';
    public const BADGEUSE_NOT_FOUND = 'BADGEUSE_NOT_FOUND';
    public const USER_NOT_FOUND = 'USER_NOT_FOUND';
    public const ACCESS_DENIED = 'ACCESS_DENIED';
    public const ZONE_ACCESS_DENIED = 'ZONE_ACCESS_DENIED';
    public const SECONDARY_ACCESS_DENIED = 'SECONDARY_ACCESS_DENIED';
    public const NO_ZONES_CONFIGURED = 'NO_ZONES_CONFIGURED';
    public const INVALID_TYPE = 'INVALID_TYPE';
    public const NO_PRINCIPAL_SERVICE = 'NO_PRINCIPAL_SERVICE';

    private string $errorCode;

    public function __construct(string $errorCode, string $message = '', \Throwable $previous = null)
    {
        $this->errorCode = $errorCode;
        parent::__construct($message ?: $this->getDefaultMessage($errorCode), 0, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    private function getDefaultMessage(string $errorCode): string
    {
        return match ($errorCode) {
            self::BADGE_NOT_FOUND => 'Badge non trouvé',
            self::BADGE_EXPIRED => 'Badge expiré',
            self::NO_ACTIVE_BADGE => 'Aucun badge actif trouvé',
            self::BADGEUSE_NOT_FOUND => 'Badgeuse non trouvée',
            self::USER_NOT_FOUND => 'Utilisateur non trouvé',
            self::ACCESS_DENIED => 'Accès refusé',
            self::ZONE_ACCESS_DENIED => 'Accès refusé à cette zone',
            self::SECONDARY_ACCESS_DENIED => 'Vous devez d\'abord pointer dans votre service principal',
            self::NO_ZONES_CONFIGURED => 'Aucune zone configurée pour cette badgeuse',
            self::INVALID_TYPE => 'Type de badgeage invalide',
            self::NO_PRINCIPAL_SERVICE => 'Aucun service principal trouvé',
            default => 'Erreur inconnue'
        };
    }

    public function toArray(): array
    {
        return [
            'success' => false,
            'error' => $this->errorCode,
            'message' => $this->getMessage()
        ];
    }
}